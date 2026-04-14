<?php
namespace App\Service;

class GroqService
{
    private string $apiKey;
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL   = 'llama-3.3-70b-versatile';

    public function __construct(string $groqApiKey)
    {
        $this->apiKey = $groqApiKey;
    }

    public function ask(string $userMessage, string $systemPrompt = ''): string
    {
        $messages = [];
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = $this->sendRequest($messages);
        return $response['choices'][0]['message']['content'] ?? 'Aucune reponse recue.';
    }

    private function sendRequest(array $messages): array
    {
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'    => self::MODEL,
                'messages' => $messages,
            ]),
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true) ?? [];
    }
    public function traduire(string $texte, string $langue): string
    {
        $systemPrompt = "Tu es un traducteur professionnel. Traduis uniquement le texte fourni, sans explication ni commentaire.";
        $userMessage  = "Traduis ce texte en $langue :\n\n$texte";

        $response = $this->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userMessage],
        ]);

        return $response['choices'][0]['message']['content'] ?? 'Traduction indisponible.';
    }
    public function ameliorer(string $texte, string $contexte = ''): string
    {
        $systemPrompt = 'Tu es un expert en redaction professionnelle. Ameliore le texte fourni pour le rendre plus clair, professionnel et persuasif. Retourne uniquement le texte ameliore, sans explication.';
        $userMessage = $contexte ? 'Contexte : ' . $contexte . '

Texte a ameliorer :

' . $texte : 'Texte a ameliorer :

' . $texte;
        $response = $this->sendRequest([['role' => 'system', 'content' => $systemPrompt], ['role' => 'user', 'content' => $userMessage]]);
        return $response['choices'][0]['message']['content'] ?? 'Amelioration indisponible.';
    }
}