<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CurrencyExchangeService
{
    private const API_URL = 'https://api.frankfurter.dev/v1/latest';
    private const TND_TO_EUR = 0.29;
    private const CACHE_KEY = 'currency_rates_tnd';
    private const CACHE_TTL = 86400;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache
    ) {}

    /**
     * Get exchange rates from 1 DT to EUR and USD.
     * Since Frankfurter doesn't support TND, we use a fixed TND→EUR rate
     * and fetch EUR→USD from the API.
     *
     * @return array{EUR: float, USD: float}
     */
    public function getRates(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_TTL);

            try {
                $response = $this->httpClient->request('GET', self::API_URL, [
                    'query' => [
                        'base' => 'EUR',
                        'symbols' => 'USD',
                    ],
                ]);

                $data = $response->toArray();
                $eurToUsd = $data['rates']['USD'] ?? 1.08;

                return [
                    'EUR' => self::TND_TO_EUR,
                    'USD' => round(self::TND_TO_EUR * $eurToUsd, 4),
                ];
            } catch (\Throwable $e) {
                $item->expiresAfter(3600);
                return ['EUR' => 0.29, 'USD' => 0.32];
            }
        });
    }

    /**
     * Convert an amount from DT to EUR and USD.
     *
     * @return array{EUR: float, USD: float}
     */
    public function convert(float $amountDT): array
    {
        $rates = $this->getRates();
        return [
            'EUR' => round($amountDT * $rates['EUR'], 2),
            'USD' => round($amountDT * $rates['USD'], 2),
        ];
    }
}
