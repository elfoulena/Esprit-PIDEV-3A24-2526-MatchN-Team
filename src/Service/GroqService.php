<?php
namespace App\Service;

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
    
    // ... reste du code identique mais remplacer self::API_KEY par $this->apiKey
    private const SYSTEM_AMELIORER =
        "Tu es un assistant spécialisé dans la rédaction de réponses professionnelles " .
        "à des réclamations RH. Améliore le texte fourni en respectant ces règles :\n" .
        "1. Corriger toutes les fautes d'orthographe et de grammaire.\n" .
        "2. Rendre le ton formel, respectueux et professionnel.\n" .
        "3. Supprimer toute répétition inutile.\n" .
        "4. Conserver le sens et l'intention originale.\n" .
        "5. Ne pas inventer d'informations.\n" .
        "6. Répondre UNIQUEMENT avec le texte amélioré, sans introduction ni commentaire.";

    private const SYSTEM_TRADUIRE =
        "Tu es un traducteur professionnel. " .
        "Traduis le texte fourni en respectant ces règles :\n" .
        "1. Réponds UNIQUEMENT avec la traduction.\n" .
        "2. Pas d'introduction, pas d'explication, pas de guillemets.\n" .
        "3. Conserve le sens exact du message original.";

    public function ameliorer(string $texte, string $contexte = ''): string
    {
        $prompt = "Voici ma réponse à améliorer :\n\n" . $texte;
        if ($contexte) {
            $prompt .= "\n\n[Contexte de la réclamation : " . $contexte . "]";
        }
        return $this->callGroq($prompt, self::SYSTEM_AMELIORER);
    }

    public function traduire(string $texte, string $langue): string
    {
        $langueNom = $langue === 'en' ? 'English' : 'Spanish';
        $prompt = "Traduis le texte suivant du français vers le " . $langueNom . ".\n\n" .
                  "Texte à traduire :\n" . $texte;
        return $this->callGroq($prompt, self::SYSTEM_TRADUIRE);
    }

    private function callGroq(string $userMessage, string $systemPrompt): string
    {
        $data = [
            'model'       => self::MODEL,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userMessage],
            ],
            'max_tokens'  => 1024,
            'temperature' => 0.3,
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::API_KEY,
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('Erreur cURL : ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException('Erreur API Groq HTTP ' . $httpCode . ' : ' . $response);
        }

        $json = json_decode($response, true);
        return $json['choices'][0]['message']['content'] ?? '';
    }
}