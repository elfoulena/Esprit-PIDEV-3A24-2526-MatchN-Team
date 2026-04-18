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
        $userMessage = $contexte ? 'Contexte : ' . $contexte . "\n\nTexte a ameliorer :\n\n" . $texte : "Texte a ameliorer :\n\n" . $texte;
        $response = $this->sendRequest([['role' => 'system', 'content' => $systemPrompt], ['role' => 'user', 'content' => $userMessage]]);
        return $response['choices'][0]['message']['content'] ?? 'Amelioration indisponible.';
    }

    public function analyserStatistiquesEvenements(array $stats): string
    {
        $repartitionText = '';
        foreach ($stats['repartition_types'] as $item) {
            $repartitionText .= "- {$item['type_evenement']} : {$item['count']} événement(s)\n";
        }

        $systemPrompt = "Tu es un expert en gestion événementielle. Tu analyses des statistiques d'événements d'entreprise et tu fournis des recommandations concrètes, claires et actionnables pour améliorer l'organisation des prochains événements. Réponds en français, avec des paragraphes courts.";

        $userMessage = "Voici les statistiques des événements de notre plateforme :\n\n"
            . "- Nombre total d'événements : {$stats['total_evenements']}\n"
            . "- Taux de remplissage moyen : {$stats['taux_remplissage_moyen_pourcentage']}%\n"
            . "- Répartition par type :\n{$repartitionText}\n"
            . "En te basant sur ces chiffres, donne une analyse de la performance, identifie les points forts et les points faibles, et propose 3 recommandations stratégiques pour améliorer la participation et diversifier les types d'événements.";

        $response = $this->sendRequest([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userMessage],
        ]);

        return $response['choices'][0]['message']['content'] ?? 'Analyse indisponible.';
    }
}