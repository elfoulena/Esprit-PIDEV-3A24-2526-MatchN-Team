<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HCaptchaService
{
    private HttpClientInterface $client;
    private string $secret;

    public function __construct(HttpClientInterface $client, string $hcaptchaSecret)
    {
        $this->client = $client;
        $this->secret = $hcaptchaSecret;
    }

    public function verify(string $token): bool
    {
        $response = $this->client->request('POST', 'https://hcaptcha.com/siteverify', [
            'body' => [
                'secret' => $this->secret,
                'response' => $token,
            ],
        ]);

        $data = $response->toArray();

        return $data['success'] ?? false;
    }
}