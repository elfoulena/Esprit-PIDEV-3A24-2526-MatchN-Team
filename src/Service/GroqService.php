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

        $response = $this->sendRequest($messages);

        return $response['choices'][0]['message']['content'] ?? 'Aucune reponse recue.';
    }

    /**
     * @param list<array{role: string, content: string}> $messages
     * @return array<string, mixed>
     */
    private function sendRequest(array $messages): array
    {
        $payload = json_encode([
            'model' => self::MODEL,
            'messages' => $messages,
        ]);
        if (!is_string($payload) || $payload === '') {
            return [];
        }

        $ch = curl_init(self::API_URL);
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        if (!is_string($result) || $result === '') {
            return [];
        }

        $decoded = json_decode($result, true);

        return is_array($decoded) ? $decoded : [];
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

    /**
     * @param array{
     *   repartition_types?: list<array{type_evenement?: string, count?: int|string}>,
     *   total_evenements?: int|string,
     *   taux_remplissage_moyen_pourcentage?: float|int|string
     * } $stats
     */
    public function analyserStatistiquesEvenements(array $stats): string
    {
        $repartitionText = '';
        foreach (($stats['repartition_types'] ?? []) as $item) {
            $type = isset($item['type_evenement']) && is_string($item['type_evenement']) ? $item['type_evenement'] : 'Inconnu';
            $count = $item['count'] ?? 0;
            $repartitionText .= "- {$type} : {$count} evenement(s)\n";
        }

        $systemPrompt = "Tu es un expert en gestion evenementielle. Tu analyses des statistiques d'evenements d'entreprise et tu fournis des recommandations concretes, claires et actionnables pour ameliorer l'organisation des prochains evenements. Reponds en francais, avec des paragraphes courts.";

        $userMessage = "Voici les statistiques des evenements de notre plateforme :\n\n"
            . '- Nombre total d\'evenements : ' . ($stats['total_evenements'] ?? 0) . "\n"
            . '- Taux de remplissage moyen : ' . ($stats['taux_remplissage_moyen_pourcentage'] ?? 0) . "%\n"
            . "- Repartition par type :\n{$repartitionText}\n"
            . "En te basant sur ces chiffres, donne une analyse de la performance, identifie les points forts et les points faibles, et propose 3 recommandations strategiques pour ameliorer la participation et diversifier les types d'evenements.";

        $response = $this->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ]);

        return $response['choices'][0]['message']['content'] ?? 'Analyse indisponible.';
    }
}
