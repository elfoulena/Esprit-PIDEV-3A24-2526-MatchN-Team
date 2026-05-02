<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleSheetsService
{
    private ?string $credentialsPath;
    private ?string $sheetId;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger, HttpClientInterface $httpClient)
    {
        $this->credentialsPath = $params->get('app.google_sheets_credentials_path');
        $this->sheetId = $params->get('app.google_sheet_id');
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function appendParticipationInfo(string $nom, string $prenom, string $email, string $evenementTitre, \DateTimeInterface $date): bool
    {
        if (!$this->credentialsPath || !$this->sheetId) {
            $this->logger->error('Google Sheets: Missing credentials path or sheet ID in config.');
            return false;
        }

        if (!file_exists($this->credentialsPath)) {
            $this->logger->error('Google Sheets: Credentials file NOT FOUND at: ' . $this->credentialsPath);
            return false;
        }

        if (!is_readable($this->credentialsPath)) {
            $this->logger->error('Google Sheets: Credentials file exists but is NOT READABLE: ' . $this->credentialsPath);
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return false;
            }

            $values = [
                [
                    $nom,
                    $prenom,
                    $email,
                    $evenementTitre,
                    $date->format('Y-m-d H:i:s'),
                    'Présent'
                ]
            ];

            $range = 'Feuille 1!A:F';
            $url = sprintf(
                'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append?valueInputOption=USER_ENTERED',
                $this->sheetId,
                $range
            );

            $this->logger->info('Google Sheets Lite: Sending request to append...');
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'values' => $values
                ],
                'timeout' => 2 // Prevent hanging
            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('Google Sheets Lite: Response received with status ' . $statusCode);

            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            }

            $this->logger->error('Google Sheets API Error response: ' . $response->getContent(false));
            return false;

        } catch (\Exception $e) {
            $this->logger->error('Google Sheets Lite Error: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken(): ?string
    {
        try {
            $json = json_decode(file_get_contents($this->credentialsPath), true);
            $now = time();
            
            $payload = [
                'iss' => $json['client_email'],
                'scope' => 'https://www.googleapis.com/auth/spreadsheets',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600
            ];

            $jwt = JWT::encode($payload, $json['private_key'], 'RS256');

            $this->logger->info('Google Auth: Requesting token...');
            $response = $this->httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
                'body' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ],
                'timeout' => 2 // Prevent hanging
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Google Auth Error: ' . $response->getContent(false));
                return null;
            }

            $this->logger->info('Google Auth: Token obtained successfully.');

            $data = $response->toArray();
            return $data['access_token'] ?? null;

        } catch (\Exception $e) {
            $this->logger->error('Google Auth JWT Error: ' . $e->getMessage());
            return null;
        }
    }
}
