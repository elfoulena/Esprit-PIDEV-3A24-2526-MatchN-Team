<?php

namespace App\Tests\Unit\Service;

use App\Entity\Projet;
use App\Service\GitHubRepositoryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GitHubRepositoryServiceTest extends TestCase
{
    public function testCreateRepositoryForProjectThrowsWhenTokenMissing(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::never())->method('request');

        $service = new GitHubRepositoryService(
            $httpClient,
            new AsciiSlugger(),
            '',
            'my-org',
            true
        );

        $projet = new Projet();
        $projet->setId_projet(12)->setTitre('Projet CRM');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('GITHUB_TOKEN manquant');

        $service->createRepositoryForProject($projet);
    }

    public function testCreateRepositoryForProjectUsesOrganizationEndpointWhenAvailable(): void
    {
        $requests = [];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);
        $response->method('toArray')->willReturn([
            'name' => 'matchnteam-12-projet-crm',
            'html_url' => 'https://github.com/my-org/matchnteam-12-projet-crm',
            'owner' => ['login' => 'my-org'],
            'private' => true,
        ]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturnCallback(static function (string $method, string $url, array $options) use (&$requests, $response) {
                $requests[] = [$method, $url, $options];
                return $response;
            });

        $service = new GitHubRepositoryService(
            $httpClient,
            new AsciiSlugger(),
            'token-123',
            'my-org',
            true
        );

        $projet = new Projet();
        $projet->setId_projet(12)->setTitre('Projet CRM');

        $result = $service->createRepositoryForProject($projet);

        self::assertSame('matchnteam-12-projet-crm', $result['name']);
        self::assertSame('https://github.com/my-org/matchnteam-12-projet-crm', $result['html_url']);
        self::assertSame('my-org', $result['owner']);
        self::assertTrue($result['private']);

        self::assertCount(1, $requests);
        self::assertSame('POST', $requests[0][0]);
        self::assertSame('https://api.github.com/orgs/my-org/repos', $requests[0][1]);
        self::assertSame('matchnteam-12-projet-crm', $requests[0][2]['json']['name']);
        self::assertSame(true, $requests[0][2]['json']['private']);
    }

    public function testCreateRepositoryForProjectFallsBackToUserEndpointOn403Or404(): void
    {
        $requests = [];

        $orgResponse = $this->createMock(ResponseInterface::class);
        $orgResponse->method('getStatusCode')->willReturn(404);
        $orgResponse->method('toArray')->willReturn(['message' => 'Not Found']);

        $userResponse = $this->createMock(ResponseInterface::class);
        $userResponse->method('getStatusCode')->willReturn(201);
        $userResponse->method('toArray')->willReturn([
            'name' => 'matchnteam-7-site-web',
            'html_url' => 'https://github.com/personal/matchnteam-7-site-web',
            'owner' => ['login' => 'personal'],
            'private' => false,
        ]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::exactly(2))
            ->method('request')
            ->willReturnCallback(static function (string $method, string $url, array $options) use (&$requests, $orgResponse, $userResponse) {
                $requests[] = [$method, $url, $options];
                return count($requests) === 1 ? $orgResponse : $userResponse;
            });

        $service = new GitHubRepositoryService(
            $httpClient,
            new AsciiSlugger(),
            'token-123',
            'my-org',
            false
        );

        $projet = new Projet();
        $projet->setId_projet(7)->setTitre('Site Web');

        $result = $service->createRepositoryForProject($projet);

        self::assertSame('matchnteam-7-site-web', $result['name']);
        self::assertSame('personal', $result['owner']);
        self::assertFalse($result['private']);

        self::assertSame('https://api.github.com/orgs/my-org/repos', $requests[0][1]);
        self::assertSame('https://api.github.com/user/repos', $requests[1][1]);
    }

    public function testCreateRepositoryForProjectThrowsWhenGithubReturnsError(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(422);
        $response->method('toArray')->willReturn(['message' => 'Repository creation failed']);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with('POST', 'https://api.github.com/user/repos', self::isType('array'))
            ->willReturn($response);

        $service = new GitHubRepositoryService(
            $httpClient,
            new AsciiSlugger(),
            'token-123',
            '',
            true
        );

        $projet = new Projet();
        $projet->setId_projet(3)->setTitre('API Mobile');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Repository creation failed');

        $service->createRepositoryForProject($projet);
    }
}

