<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private const API_URL  = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL    = 'llama-3.1-8b-instant';
    public function __construct(private readonly HttpClientInterface $httpClient) {}

    public function genererDescriptionCompetence(string $nomCompetence): string
    {
        $prompt = "Rédige une description technique et concise (environ 20 mots) "
                . "pour la compétence informatique suivante : $nomCompetence. "
                . "La description doit expliquer l'utilité de cette compétence dans un cadre professionnel.";

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::API_KEY,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => self::MODEL,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ],
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? 'Description non disponible.';

        } catch (\Throwable $e) {
            return "Description en attente de rédaction pour $nomCompetence";
        }
    }
}