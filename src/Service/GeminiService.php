<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Process\Process;

class GeminiService
{
    private const API_URL  = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL    = 'llama-3.1-8b-instant';

    private const API_KEY  = ' ';

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

    public function generateMatchExplanation(
        string $nomComplet,
        array $freelancerSkills,
        string $projetTitre,
        array $requiredSkills
    ): string {
        $skillsFL  = implode(', ', $freelancerSkills);
        $skillsReq = implode(', ', $requiredSkills);

        $prompt = "Tu es un assistant RH. Explique en 2 phrases maximum (environ 35 mots) "
            . "pourquoi le freelancer \"$nomComplet\" est un bon candidat pour le projet \"$projetTitre\". "
            . "Compétences du freelancer : $skillsFL. "
            . "Compétences requises par le projet : $skillsReq. "
            . "Réponds en français, de manière concise et professionnelle, sans introduction ni formule de politesse.";

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::API_KEY,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => self::MODEL,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.5,
                    'max_tokens'  => 120,
                ],
                'timeout' => 10,
            ]);

            $data = $response->toArray();
            return trim($data['choices'][0]['message']['content'] ?? 'Analyse IA indisponible.');
        } catch (\Throwable $e) {
            return 'Analyse IA indisponible pour le moment.';
        }
    }

    public function extractProjectDataFromText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $prompt = <<<PROMPT
Tu es un assistant de gestion de projet.
Analyse le texte d'un cahier des charges et retourne UNIQUEMENT un JSON valide (sans markdown, sans commentaire) avec ce schema exact:
{
  "titre": "string",
  "description": "string",
  "statut": "PLANIFIE|EN_COURS|EN_PAUSE|TERMINE|ANNULE",
  "priorite": "HAUTE|MOYENNE|BASSE",
  "dateDebut": "YYYY-MM-DD",
  "dateFin": "YYYY-MM-DD",
  "dateLivraison": "YYYY-MM-DD",
  "budgetTotal": number,
  "budgetInterne": number,
  "budgetFreelance": number,
  "visibleEmploye": true,
  "visibleFreelancer": true,
  "competenceKeywords": ["string", "string"]
}

Regles:
- Si une valeur manque, fais une estimation raisonnable.
- Respecte strictement les enums.
- Les budgets doivent etre des nombres positifs.
- dateFin et dateLivraison doivent etre >= dateDebut.
- competenceKeywords: 3 a 10 mots-cles techniques courts.

Texte a analyser:
{$text}
PROMPT;

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::API_KEY,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => self::MODEL,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.2,
                    'max_tokens'  => 900,
                ],
                'timeout' => 45,
            ]);

            $data = $response->toArray();
            $raw = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            if ($raw === '') {
                return [];
            }

            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            if (preg_match('/\{[\s\S]*\}/', $raw, $matches) === 1) {
                $decoded = json_decode($matches[0], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function extractProjectDataFromPdfPath(string $pdfPath): array
    {
        $text = $this->extractTextFromPdf($pdfPath);
        if (trim($text) === '') {
            return [];
        }

        $byLabels = $this->extractProjectDataByLabels($text);
        if (count($byLabels) >= 4) {
            return $byLabels;
        }
        $aiData = $this->extractProjectDataFromText($text);
        $combined = array_replace($aiData, $byLabels);
        if (!empty($combined)) {
            return $combined;
        }

        return $this->buildFallbackProjectDataFromText($text);
    }

    private function extractTextFromPdf(string $pdfPath): string
    {
        if (!is_file($pdfPath)) {
            return '';
        }

        $text = $this->extractTextWithPdfToText($pdfPath);
        if (trim($text) !== '') {
            return $this->sanitizeExtractedText($text);
        }

        $text = $this->extractTextWithPythonPypdf($pdfPath);
        if (trim($text) !== '') {
            return $this->sanitizeExtractedText($text);
        }

        $binary = @file_get_contents($pdfPath);
        if (!is_string($binary) || $binary === '') {
            return '';
        }

        return $this->sanitizeExtractedText($this->extractTextFromPdfBinary($binary));
    }

    private function extractTextWithPdfToText(string $pdfPath): string
    {
        try {
            $process = new Process(['pdftotext', '-layout', $pdfPath, '-']);
            $process->setTimeout(15);
            $process->run();

            if ($process->isSuccessful()) {
                return (string) $process->getOutput();
            }
        } catch (\Throwable) {
        }

        return '';
    }

    private function extractTextWithPythonPypdf(string $pdfPath): string
    {
        $script = <<<'PY'
from pypdf import PdfReader
import sys
pdf = sys.argv[1]
reader = PdfReader(pdf)
texts = []
for page in reader.pages:
    t = page.extract_text() or ""
    if t:
        texts.append(t)
print("\n".join(texts))
PY;

        try {
            $process = new Process(['python', '-c', $script, $pdfPath]);
            $process->setTimeout(20);
            $process->run();

            if ($process->isSuccessful()) {
                return (string) $process->getOutput();
            }
        } catch (\Throwable) {
        }

        return '';
    }

    private function extractTextFromPdfBinary(string $binary): string
    {
        $chunks = [];

        if (preg_match_all('/\((?:\\\\.|[^\\\\\)])+\)\s*T[Jj]/s', $binary, $m1) > 0) {
            foreach ($m1[0] as $match) {
                if (preg_match('/^\((.*)\)\s*T[Jj]$/s', $match, $inner) === 1) {
                    $chunks[] = $this->decodePdfLiteralString($inner[1]);
                }
            }
        }

        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $binary, $m2) > 0) {
            foreach ($m2[1] as $arr) {
                if (preg_match_all('/\((?:\\\\.|[^\\\\\)])+\)/s', $arr, $lits) > 0) {
                    foreach ($lits[0] as $lit) {
                        $lit = substr($lit, 1, -1);
                        $chunks[] = $this->decodePdfLiteralString($lit);
                    }
                }
            }
        }

        return implode("\n", $chunks);
    }

    private function decodePdfLiteralString(string $value): string
    {
        $value = str_replace(["\\n", "\\r", "\\t", "\\b", "\\f"], ["\n", "\r", "\t", "\b", "\f"], $value);
        $value = preg_replace('/\\\\([()\\\\])/', '$1', $value) ?? $value;
        $value = preg_replace_callback('/\\\\([0-7]{1,3})/', static function (array $m): string {
            return chr(octdec($m[1]));
        }, $value) ?? $value;

        return $value;
    }

    private function sanitizeExtractedText(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252, ASCII');
        }

        $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        if (is_string($fixed) && $fixed !== '') {
            $text = $fixed;
        }

        $text = preg_replace('/[^\P{C}\n\t]/u', ' ', $text) ?? $text;
        $text = preg_replace('/[ \t]{2,}/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function extractProjectDataByLabels(string $text): array
    {
        $searchText = $this->normalizeForSearch($text);

        $get = function (string $pattern) use ($searchText): ?string {
            if (preg_match($pattern, $searchText, $m) === 1) {
                return trim((string) ($m[1] ?? ''));
            }
            return null;
        };

        $titre = null;
        if (preg_match('/\bTitre\s*:\s*([^\r\n]+)/i', $text, $mTitle) === 1) {
            $titre = trim($mTitle[1]);
        } else {
            $titre = $get('/\btitre\s*:\s*([^\r\n]+)/i');
        }

        $description = null;
        if (preg_match('/\bDescription\s*:\s*(.+?)\s*(?=\n(?:Date|Statut|Budget|Priorit|Visible)\b|\z)/is', $text, $m) === 1) {
            $description = trim($m[1]);
        } elseif (preg_match('/\bdescription\s*:\s*(.+?)\s*(?=\n(?:date|statut|budget|priorit|visible)\b|\z)/is', $searchText, $m) === 1) {
            $description = trim($m[1]);
        }

        $dateDebut = $this->normalizeDateString((string) $get('/\bdate\s+de\s+d\S*but\s*:\s*([0-9\/\-]{8,10})/i'));
        $dateFin = $this->normalizeDateString((string) $get('/\bdate\s+de\s+fin\s*:\s*([0-9\/\-]{8,10})/i'));
        $dateLivraison = $this->normalizeDateString((string) $get('/\bdate\s+de\s+livraison\s*:\s*([0-9\/\-]{8,10})/i'));

        $statutRaw = $get('/\bstatut\s*:\s*([^\r\n]+)/i');
        $prioriteRaw = $get('/\bpriorit\S*\s*:\s*([^\r\n]+)/i');
        $visibleEmployeRaw = $get('/\bvisible\s+employ\S*\s*:\s*([^\r\n]+)/i');
        $visibleFreelancerRaw = $get('/\bvisible\s+freelancer\s*:\s*([^\r\n]+)/i');

        $budgetTotal = $this->extractLabeledAmount($searchText, '/\bbudget\s+total\s*:\s*([0-9][0-9\s\.,]*)/i');
        $budgetInterne = $this->extractLabeledAmount($searchText, '/\bbudget\s+interne\s*:\s*([0-9][0-9\s\.,]*)/i');
        $budgetFreelance = $this->extractLabeledAmount($searchText, '/\bbudget\s+freelance\s*:\s*([0-9][0-9\s\.,]*)/i');

        return array_filter([
            'titre' => $titre,
            'description' => $description,
            'statut' => $this->normalizeStatus($statutRaw),
            'priorite' => $this->normalizePriority($prioriteRaw),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'dateLivraison' => $dateLivraison,
            'budgetTotal' => $budgetTotal,
            'budgetInterne' => $budgetInterne,
            'budgetFreelance' => $budgetFreelance,
            'visibleEmploye' => $this->parseOuiNon($visibleEmployeRaw),
            'visibleFreelancer' => $this->parseOuiNon($visibleFreelancerRaw),
        ], static fn($v): bool => $v !== null && $v !== '');
    }

    private function normalizeForSearch(string $text): string
    {
        $normalized = $text;
        if (!mb_check_encoding($normalized, 'UTF-8')) {
            $normalized = mb_convert_encoding($normalized, 'UTF-8', 'auto');
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        if (is_string($ascii) && $ascii !== '') {
            $normalized = $ascii;
        }

        return mb_strtolower($normalized);
    }

    private function extractLabeledAmount(string $text, string $pattern): ?float
    {
        if (preg_match($pattern, $text, $m) !== 1) {
            return null;
        }

        $value = str_replace([' ', ','], ['', '.'], $m[1]);
        if (!is_numeric($value)) {
            return null;
        }

        $num = (float) $value;
        return $num >= 0 ? round($num, 2) : null;
    }

    private function parseOuiNon(?string $raw): ?bool
    {
        if (!is_string($raw)) {
            return null;
        }
        $v = mb_strtolower(trim($raw));
        if (str_starts_with($v, 'oui')) return true;
        if (str_starts_with($v, 'non')) return false;
        return null;
    }

    private function normalizeStatus(?string $raw): ?string
    {
        if (!is_string($raw)) return null;
        $v = mb_strtolower(trim($raw));
        return match (true) {
            str_contains($v, 'en cours') => 'EN_COURS',
            str_contains($v, 'en pause') => 'EN_PAUSE',
            str_contains($v, 'termin') => 'TERMINE',
            str_contains($v, 'annul') => 'ANNULE',
            str_contains($v, 'planifi') => 'PLANIFIE',
            default => null,
        };
    }

    private function normalizePriority(?string $raw): ?string
    {
        if (!is_string($raw)) return null;
        $v = mb_strtolower(trim($raw));
        return match (true) {
            str_contains($v, 'haute') => 'HAUTE',
            str_contains($v, 'moyenne') => 'MOYENNE',
            str_contains($v, 'basse') => 'BASSE',
            default => null,
        };
    }

    private function buildFallbackProjectDataFromText(string $text): array
    {
        $lines = preg_split('/\R+/', $text) ?: [];
        $title = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (mb_strlen($line) >= 6) {
                $title = mb_substr($line, 0, 255);
                break;
            }
        }

        $description = trim(mb_substr($text, 0, 3000));
        if ($description === '') {
            return [];
        }

        $dates = $this->extractDates($text);
        $budgets = $this->extractBudgets($text);

        return array_filter([
            'titre' => $title !== '' ? $title : null,
            'description' => $description,
            'statut' => $this->detectStatus($text),
            'priorite' => $this->detectPriority($text),
            'dateDebut' => $dates['dateDebut'] ?? null,
            'dateFin' => $dates['dateFin'] ?? null,
            'dateLivraison' => $dates['dateLivraison'] ?? null,
            'budgetTotal' => $budgets['budgetTotal'] ?? null,
            'budgetInterne' => $budgets['budgetInterne'] ?? null,
            'budgetFreelance' => $budgets['budgetFreelance'] ?? null,
        ], static fn($v): bool => $v !== null && $v !== '');
    }

    private function extractDates(string $text): array
    {
        $res = [];
        if (preg_match_all('/\b(\d{4}-\d{2}-\d{2}|\d{2}[\/\-]\d{2}[\/\-]\d{4})\b/', $text, $m) > 0) {
            $normalized = [];
            foreach ($m[1] as $raw) {
                $n = $this->normalizeDateString($raw);
                if ($n !== null) {
                    $normalized[] = $n;
                }
            }
            $normalized = array_values(array_unique($normalized));
            if (isset($normalized[0])) $res['dateDebut'] = $normalized[0];
            if (isset($normalized[1])) $res['dateFin'] = $normalized[1];
            if (isset($normalized[2])) $res['dateLivraison'] = $normalized[2];
        }
        return $res;
    }

    private function normalizeDateString(string $raw): ?string
    {
        $raw = trim($raw);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
        foreach ($formats as $f) {
            $dt = \DateTimeImmutable::createFromFormat($f, $raw);
            if ($dt instanceof \DateTimeImmutable) {
                return $dt->format('Y-m-d');
            }
        }
        return null;
    }

    private function extractBudgets(string $text): array
    {
        $res = [];
        $map = [
            'budgetTotal' => '/budget\s*total[^0-9]{0,20}([0-9][0-9\s\.,]*)/i',
            'budgetInterne' => '/budget\s*interne[^0-9]{0,20}([0-9][0-9\s\.,]*)/i',
            'budgetFreelance' => '/budget\s*(freelance|externe)[^0-9]{0,20}([0-9][0-9\s\.,]*)/i',
        ];

        foreach ($map as $key => $pattern) {
            if (preg_match($pattern, $text, $m) === 1) {
                $value = $m[count($m) - 1];
                $value = str_replace([' ', ','], ['', '.'], $value);
                if (is_numeric($value)) {
                    $num = (float) $value;
                    if ($num >= 0) {
                        $res[$key] = round($num, 2);
                    }
                }
            }
        }

        return $res;
    }

    private function detectPriority(string $text): ?string
    {
        $t = mb_strtolower($text);
        if (str_contains($t, 'priorite haute') || str_contains($t, 'urgent')) return 'HAUTE';
        if (str_contains($t, 'priorite basse')) return 'BASSE';
        if (str_contains($t, 'priorite moyenne')) return 'MOYENNE';
        return null;
    }

    private function detectStatus(string $text): ?string
    {
        $t = mb_strtolower($text);
        if (str_contains($t, 'en cours')) return 'EN_COURS';
        if (str_contains($t, 'en pause')) return 'EN_PAUSE';
        if (str_contains($t, 'termine')) return 'TERMINE';
        if (str_contains($t, 'annule')) return 'ANNULE';
        if (str_contains($t, 'planifie')) return 'PLANIFIE';
        return null;
    }
}
