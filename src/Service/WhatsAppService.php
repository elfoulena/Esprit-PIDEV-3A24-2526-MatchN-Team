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

    public function sendWhatsAppMessage(string $to, string $message, string $templateSid = null): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        
        $postData = [
            'To' => 'whatsapp:' . $to,
            'From' => 'whatsapp:' . $this->fromNumber,
            'Body' => $message  // This works for replies within 24h window
        ];
        
        $ch = curl_init();
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
        
        if ($curlError) {
            $this->logger->error('cURL Error: ' . $curlError);
            return [
                'success' => false,
                'error' => 'Connection error: ' . $curlError
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 201) {
            return [
                'success' => true,
                'sid' => $responseData['sid'] ?? null,
                'status' => $responseData['status'] ?? 'queued'
            ];
        }
        
        $errorMessage = $responseData['message'] ?? 'Failed to send message';
        $this->logger->error('Twilio API Error: ' . $errorMessage, ['response' => $responseData]);
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'details' => $responseData
        ];
    }

    public function sendWhatsAppTemplate(string $to, string $templateSid, array $variables = []): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        
        $postData = [
            'To' => 'whatsapp:' . $to,
            'From' => 'whatsapp:' . $this->fromNumber,
            'ContentSid' => $templateSid,
        ];
        
        // Add content variables if provided
        if (!empty($variables)) {
            $postData['ContentVariables'] = json_encode($variables);
        }
        
        $ch = curl_init();
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
        
        if ($curlError) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $curlError
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 201) {
            return [
                'success' => true,
                'sid' => $responseData['sid'] ?? null,
                'status' => $responseData['status'] ?? 'queued'
            ];
        }
        
        return [
            'success' => false,
            'error' => $responseData['message'] ?? 'Failed to send template'
        ];
    }

    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If number starts with 0, replace with country code for Tunisia
        if (strpos($phone, '0') === 0) {
            $phone = '+216' . substr($phone, 1);
        }
        
        // If no + prefix and not starting with 00, add +
        if (strpos($phone, '+') !== 0 && strpos($phone, '00') !== 0) {
            $phone = '+' . $phone;
        }
        
        // Replace 00 with + if needed
        if (strpos($phone, '00') === 0) {
            $phone = '+' . substr($phone, 2);
        }
        
        return $phone;
    }
}