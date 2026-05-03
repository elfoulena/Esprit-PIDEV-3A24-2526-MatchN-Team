<?php
// src/Service/WhatsAppService.php

namespace App\Service;

use Psr\Log\LoggerInterface;

class WhatsAppService
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;
    private LoggerInterface $logger;

    public function __construct(string $twilioSid, string $twilioAuthToken, string $twilioWhatsappFrom, LoggerInterface $logger)
    {
        $this->accountSid = $twilioSid;
        $this->authToken = $twilioAuthToken;
        $this->fromNumber = $twilioWhatsappFrom;
        $this->logger = $logger;
    }

    /**
     * Send a WhatsApp message
     * 
     * @return array{success: bool, sid?: string, status?: string, error?: string, details?: array<mixed>}
     */
    public function sendWhatsAppMessage(string $to, string $message, ?string $templateSid = null): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        
        $postData = [
            'To' => 'whatsapp:' . $to,
            'From' => 'whatsapp:' . $this->fromNumber,
            'Body' => $message
        ];
        
        $ch = curl_init();
        if ($ch === false) {
            return [
                'success' => false,
                'error' => 'Failed to initialize cURL'
            ];
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_USERPWD, $this->accountSid . ':' . $this->authToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError !== '') {
            $this->logger->error('cURL Error: ' . $curlError);
            return [
                'success' => false,
                'error' => 'Connection error: ' . $curlError
            ];
        }
        
        if (!is_string($response)) {
            return [
                'success' => false,
                'error' => 'Invalid response from API'
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if (!is_array($responseData)) {
            return [
                'success' => false,
                'error' => 'Failed to parse API response'
            ];
        }
        
        if ($httpCode === 201) {
            return [
                'success' => true,
                'sid' => $responseData['sid'] ?? '',
                'status' => $responseData['status'] ?? 'queued'
            ];
        }
        
        $errorMessage = isset($responseData['message']) && is_string($responseData['message']) ? $responseData['message'] : 'Failed to send message';
        $this->logger->error('Twilio API Error: ' . $errorMessage, ['response' => $responseData]);
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'details' => $responseData
        ];
    }

    /**
     * Send a WhatsApp template message
     * 
     * @param array<string, mixed> $variables
     * @return array{success: bool, sid?: string, status?: string, error?: string}
     */
    public function sendWhatsAppTemplate(string $to, string $templateSid, array $variables = []): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        
        $postData = [
            'To' => 'whatsapp:' . $to,
            'From' => 'whatsapp:' . $this->fromNumber,
            'ContentSid' => $templateSid,
        ];
        
        if (!empty($variables)) {
            $postData['ContentVariables'] = json_encode($variables);
        }
        
        $ch = curl_init();
        if ($ch === false) {
            return [
                'success' => false,
                'error' => 'Failed to initialize cURL'
            ];
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_USERPWD, $this->accountSid . ':' . $this->authToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError !== '') {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $curlError
            ];
        }
        
        if (!is_string($response)) {
            return [
                'success' => false,
                'error' => 'Invalid response from API'
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if (!is_array($responseData)) {
            return [
                'success' => false,
                'error' => 'Failed to parse API response'
            ];
        }
        
        if ($httpCode === 201) {
            return [
                'success' => true,
                'sid' => $responseData['sid'] ?? '',
                'status' => $responseData['status'] ?? 'queued'
            ];
        }
        
        $errorMessage = isset($responseData['message']) && is_string($responseData['message']) ? $responseData['message'] : 'Failed to send template';
        
        return [
            'success' => false,
            'error' => $errorMessage
        ];
    }

    /**
     * Format phone number to international format
     */
    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (!is_string($phone) || $phone === '') {
            return '';
        }
        
        if (strpos($phone, '0') === 0) {
            $phone = '+216' . substr($phone, 1);
        } elseif (strpos($phone, '+') !== 0 && strpos($phone, '00') !== 0) {
            $phone = '+' . $phone;
        } elseif (strpos($phone, '00') === 0) {
            $phone = '+' . substr($phone, 2);
        }
        
        return $phone;
    }
}