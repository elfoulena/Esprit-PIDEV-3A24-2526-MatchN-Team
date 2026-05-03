<?php

namespace App\Service;

use App\Entity\Projet;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubRepositoryService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SluggerInterface $slugger,
        private readonly string $githubToken,
        private readonly ?string $githubOwner,
        private readonly bool $githubRepoPrivate,
    ) {
    }

    /**
     * @return array{name: string, html_url: string, owner: string, private: bool}
     */
    public function createRepositoryForProject(Projet $projet): array
    {
        if (trim($this->githubToken) === '') {
            throw new \RuntimeException('GITHUB_TOKEN manquant. Configure-le dans .env.local.');
        }

        $repoName = $this->buildRepositoryName($projet);
        $payload = [
            'name' => $repoName,
            'description' => sprintf('Repository auto-cree pour le projet "%s"', (string) $projet->getTitre()),
            'private' => $this->githubRepoPrivate,
            'auto_init' => true,
        ];

        $owner = trim((string) $this->githubOwner);
        $response = null;
        $statusCode = 0;
        $data = [];

        // If an owner is provided, try org creation first.
        // If that fails with 404/403, fallback to personal account creation.
        if ($owner !== '') {
            $response = $this->httpClient->request(
                'POST',
                sprintf('https://api.github.com/orgs/%s/repos', rawurlencode($owner)),
                [
                    'headers' => $this->getHeaders(),
                    'json' => $payload,
                ]
            );
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);
        }

        if ($response === null || $statusCode === 404 || $statusCode === 403) {
            $response = $this->httpClient->request('POST', 'https://api.github.com/user/repos', [
                'headers' => $this->getHeaders(),
                'json' => $payload,
            ]);
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);
        }

        if ($statusCode !== 201) {
            $message = $data['message'] ?? 'Erreur GitHub inconnue.';
            throw new \RuntimeException((string) $message);
        }

        return [
            'name' => (string) ($data['name'] ?? $repoName),
            'html_url' => (string) ($data['html_url'] ?? ''),
            'owner' => (string) ($data['owner']['login'] ?? $owner),
            'private' => (bool) ($data['private'] ?? $this->githubRepoPrivate),
        ];
    }

    private function buildRepositoryName(Projet $projet): string
    {
        $titre = $projet->getTitre();
        $slug = strtolower((string) $this->slugger->slug($titre));
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'projet';
        }

        return sprintf('matchnteam-%d-%s', $projet->getIdProjet(), substr($slug, 0, 40));
    }

    /**
     * @return array<string, string>
     */
    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github+json',
            'Authorization' => 'Bearer ' . $this->githubToken,
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent' => 'MatchNTeam-App',
        ];
    }
}
