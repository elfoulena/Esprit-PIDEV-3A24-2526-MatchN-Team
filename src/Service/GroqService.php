<?php

namespace App\Service;

class GroqService
{
    private string $apiKey;
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.3-70b-versatile';

    public function __construct(string $groqApiKey)
    {
        $this->apiKey = $groqApiKey;
    }

    public function ask(string $userMessage, string $systemPrompt = ''): string
    {
        $messages = [];
        if ($systemPrompt !== '') {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = $this->sendRequest($messages, 0.3, 1024);
        return $response['choices'][0]['message']['content'] ?? 'Aucune reponse recue.';
    }

    public function traduire(string $texte, string $langue): string
    {
        $systemPrompt = 'Tu es un traducteur professionnel. Traduis uniquement le texte fourni, sans explication ni commentaire.';
        $userMessage = "Traduis ce texte en {$langue} :\n\n{$texte}";

        $response = $this->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ]);

        return $response['choices'][0]['message']['content'] ?? 'Traduction indisponible.';
    }

    public function ameliorer(string $texte, string $contexte = ''): string
    {
        $systemPrompt = 'Tu es un expert en redaction professionnelle. Ameliore le texte fourni pour le rendre plus clair, professionnel et persuasif. Retourne uniquement le texte ameliore, sans explication.';
        $userMessage = $contexte !== ''
            ? "Contexte : {$contexte}\n\nTexte a ameliorer :\n\n{$texte}"
            : "Texte a ameliorer :\n\n{$texte}";

        $response = $this->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ]);

        return $response['choices'][0]['message']['content'] ?? 'Amelioration indisponible.';
    }

    public function analyserStatistiquesEvenements(array $stats): string
    {
        $repartitionText = '';
        foreach ($stats['repartition_types'] ?? [] as $item) {
            $type = $item['type_evenement'] ?? 'Type inconnu';
            $count = (int) ($item['count'] ?? 0);
            $repartitionText .= "- {$type} : {$count} evenement(s)\n";
        }

        $systemPrompt = "Tu es un expert en gestion evenementielle. Tu analyses des statistiques d'evenements d'entreprise et tu fournis des recommandations concretes, claires et actionnables pour ameliorer l'organisation des prochains evenements. Reponds en francais, avec des paragraphes courts.";

        $total = (int) ($stats['total_evenements'] ?? 0);
        $taux = (float) ($stats['taux_remplissage_moyen_pourcentage'] ?? 0);

        $userMessage = "Voici les statistiques des evenements de notre plateforme :\n\n"
            . "- Nombre total d'evenements : {$total}\n"
            . "- Taux de remplissage moyen : {$taux}%\n"
            . "- Repartition par type :\n{$repartitionText}\n"
            . "En te basant sur ces chiffres, donne une analyse de la performance, identifie les points forts et les points faibles, et propose 3 recommandations strategiques pour ameliorer la participation et diversifier les types d'evenements.";

        $response = $this->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ], 0.4, 1024);

        return $response['choices'][0]['message']['content'] ?? 'Analyse indisponible.';
    }

    private function sendRequest(array $messages, float $temperature = 0.3, int $maxTokens = 1024): array
    {
        $payload = [
            'model' => self::MODEL,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Erreur cURL: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('Erreur API Groq HTTP ' . $httpCode . ': ' . $result);
        }

        return json_decode($result, true) ?? [];
    }
}

