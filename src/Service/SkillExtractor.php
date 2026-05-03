<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SkillExtractor
{
    public function __construct(private HttpClientInterface $client) {}

    /**
     * @return array<int, string>
     */
    public function extract(string $description): array
    {
        $response = $this->client->request('POST', 'http://127.0.0.1:8001/extract-skills', [
            'json' => [
                'description' => $description
            ]
        ]);

        return $response->toArray()['skills'];
    }
}