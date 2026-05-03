<?php

namespace App\Tests\Unit\Controller\Admin;

use App\Controller\Admin\ProjetController;
use App\Entity\Projet;
use App\Entity\Repository as ProjetRepositoryEntity;
use App\Service\GitHubRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ProjetControllerConfigureRepositoryTest extends TestCase
{
    public function testConfigureRepositoryPersistsRepositoryAndRedirects(): void
    {
        $projet = (new Projet())
            ->setId_projet(8)
            ->setTitre('Portail RH');

        $projectRepo = $this->createMock(EntityRepository::class);
        $projectRepo
            ->expects(self::once())
            ->method('find')
            ->with(8)
            ->willReturn($projet);

        $persisted = [];
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Projet::class)
            ->willReturn($projectRepo);
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted[] = $entity;
            });
        $entityManager->expects(self::once())->method('flush');

        $githubService = $this->createMock(GitHubRepositoryService::class);
        $githubService
            ->expects(self::once())
            ->method('createRepositoryForProject')
            ->with($projet)
            ->willReturn([
                'name' => 'matchnteam-8-portail-rh',
                'html_url' => 'https://github.com/acme/matchnteam-8-portail-rh',
                'owner' => 'acme',
                'private' => true,
            ]);

        $controller = new TestableProjetController();
        $controller->setCsrfValid(true);

        $response = $controller->configureRepository(
            8,
            new Request(request: ['_token' => 'valid-token']),
            $entityManager,
            $githubService
        );

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/test-route/admin_projets_index', $response->getTargetUrl());
        self::assertCount(1, $persisted);
        self::assertInstanceOf(ProjetRepositoryEntity::class, $persisted[0]);

        /** @var ProjetRepositoryEntity $savedRepository */
        $savedRepository = $persisted[0];
        self::assertSame($projet, $savedRepository->getProjet());
        self::assertSame('matchnteam-8-portail-rh', $savedRepository->getNomRepo());
        self::assertSame('matchnteam-8-portail-rh', $savedRepository->getRepoName());
        self::assertSame('https://github.com/acme/matchnteam-8-portail-rh', $savedRepository->getUrlRepo());
        self::assertSame('acme', $savedRepository->getOwner());
        self::assertTrue($savedRepository->isPrivate());
        self::assertNotNull($savedRepository->getCreatedAt());

        self::assertContains(
            ['success', 'Repo GitHub créé: https://github.com/acme/matchnteam-8-portail-rh'],
            $controller->flashMessages
        );
    }

    public function testConfigureRepositorySkipsApiWhenRepositoryAlreadyExists(): void
    {
        $projet = (new Projet())
            ->setId_projet(10)
            ->setTitre('Intranet');
        $projet->setRepository((new ProjetRepositoryEntity())->setRepoName('existing-repo'));

        $projectRepo = $this->createMock(EntityRepository::class);
        $projectRepo
            ->expects(self::once())
            ->method('find')
            ->with(10)
            ->willReturn($projet);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Projet::class)
            ->willReturn($projectRepo);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $githubService = $this->createMock(GitHubRepositoryService::class);
        $githubService->expects(self::never())->method('createRepositoryForProject');

        $controller = new TestableProjetController();
        $controller->setCsrfValid(true);

        $response = $controller->configureRepository(
            10,
            new Request(request: ['_token' => 'valid-token']),
            $entityManager,
            $githubService
        );

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/test-route/admin_projets_index', $response->getTargetUrl());
        self::assertContains(['info', 'Ce projet a déjà un repo configuré.'], $controller->flashMessages);
    }
}

class TestableProjetController extends ProjetController
{
    /** @var list<array{string, string}> */
    public array $flashMessages = [];
    private bool $csrfValid = true;

    public function setCsrfValid(bool $valid): void
    {
        $this->csrfValid = $valid;
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfValid;
    }

    protected function addFlash(string $type, mixed $message): void
    {
        $this->flashMessages[] = [$type, (string) $message];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse('/test-route/' . $route, $status);
    }
}
