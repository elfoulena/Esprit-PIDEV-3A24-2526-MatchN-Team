<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeoLocationService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCountry(string $ip): ?string
    {
        
        try {
            $response = $this->client->request(
                'GET',
                "https://ipapi.co/{$ip}/json/"
            );

            $data = $response->toArray();

            return $data['country_name'] ?? null;

        } catch (\Exception $e) {
            return null;
        }
    }
}