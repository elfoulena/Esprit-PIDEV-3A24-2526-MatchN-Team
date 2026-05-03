<?php

namespace App\Tests\Unit\Service;
use App\Controller\CompetenceFController;
use App\Entity\CompetenceF;
use App\Repository\CompetenceFRepository;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompetenceFManagerTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private CompetenceFRepository&MockObject  $repo;
    private GeminiService&MockObject          $gemini;
    private CompetenceFController             $controller;

    protected function setUp(): void
    {
        $this->em     = $this->createMock(EntityManagerInterface::class);
        $this->repo   = $this->createMock(CompetenceFRepository::class);
        $this->gemini = $this->createMock(GeminiService::class);

        $this->controller = new CompetenceFController(
            $this->em,
            $this->repo,
            $this->gemini,
        );
    }

    public function testCreateIfNotExistsRetourneExistantSansAppelerGemini(): void
    {
        $existing = (new CompetenceF())->setNom('PHP');

        $this->repo
            ->expects($this->once())
            ->method('findByName')
            ->with('PHP')
            ->willReturn($existing);

        $this->gemini
            ->expects($this->never())
            ->method('genererDescriptionCompetence');

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $result = $this->controller->createIfNotExists('PHP');

        $this->assertSame($existing, $result);
    }

    public function testCreateIfNotExistsCreeAvecDescriptionGemini(): void
    {
        $this->repo
            ->method('findByName')
            ->with('Docker')
            ->willReturn(null);

        $this->gemini
            ->expects($this->once())           
            ->method('genererDescriptionCompetence')
            ->with('Docker')
            ->willReturn('Outil de conteneurisation.');

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->controller->createIfNotExists('Docker');

        $this->assertSame('Docker',                    $result->getNom());
        $this->assertSame('Outil de conteneurisation.', $result->getDescription());
    }


    public function testCreateIfNotExistsAvecGeminiRetournantChaineVide(): void
    {
        $this->repo->method('findByName')->willReturn(null);

        $this->gemini
            ->method('genererDescriptionCompetence')
            ->willReturn('');  

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->controller->createIfNotExists('UnknownTech');

        $this->assertSame('UnknownTech', $result->getNom());
        $this->assertIsString($result->getDescription());
    }

    public function testGeminiAppeleQuandNomModifie(): void
    {
        $this->gemini
            ->expects($this->once())
            ->method('genererDescriptionCompetence')
            ->with('Kubernetes')
            ->willReturn('Orchestrateur de conteneurs.');

        $competence = (new CompetenceF())
            ->setNom('Kubernetes')
            ->setDescription('Ancienne description.');

        $ancienNom   = 'Docker';         
        $nomAChange  = $ancienNom !== $competence->getNom();

        if ($nomAChange) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom() ?? '')
            );
        }

        $this->assertSame('Orchestrateur de conteneurs.', $competence->getDescription());
    }

 
    public function testGeminiNonAppeleQuandNomInchangeEtDescriptionPresente(): void
    {
        $this->gemini
            ->expects($this->never())
            ->method('genererDescriptionCompetence');

        $competence = (new CompetenceF())
            ->setNom('Symfony')
            ->setDescription('Framework PHP robuste.');

        $ancienNom  = 'Symfony';
        $nomAChange = $ancienNom !== $competence->getNom();

        if ($nomAChange) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom() ?? '')
            );
        } elseif (empty(trim((string) $competence->getDescription()))) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom() ?? '')
            );
        }

        $this->assertSame('Framework PHP robuste.', $competence->getDescription());
    }

    public function testDescriptionAvecEspacesEstConsidereVide(): void
    {
        $this->gemini
            ->expects($this->once())
            ->method('genererDescriptionCompetence')
            ->willReturn('Description régénérée.');

        $competence = (new CompetenceF())
            ->setNom('Symfony')
            ->setDescription('   ');  

        $ancienNom  = 'Symfony';
        $nomAChange = $ancienNom !== $competence->getNom();

        if ($nomAChange) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom() ?? '')
            );
        } elseif (empty(trim((string) $competence->getDescription()))) {
            $competence->setDescription(
                $this->gemini->genererDescriptionCompetence($competence->getNom() ?? '')
            );
        }

        $this->assertSame('Description régénérée.', $competence->getDescription());
    }


    public function testCreateIfNotExistsEstIdempotent(): void
    {
        $existing = (new CompetenceF())->setNom('React');

        $this->repo
            ->expects($this->exactly(2))
            ->method('findByName')
            ->with('React')
            ->willReturnOnConsecutiveCalls(null, $existing);

        $this->gemini
            ->expects($this->once())   
            ->method('genererDescriptionCompetence')
            ->willReturn('Bibliothèque JS.');

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $first  = $this->controller->createIfNotExists('React');
        $second = $this->controller->createIfNotExists('React');

        $this->assertSame($existing, $second);
        $this->assertSame('React', $first->getNom());
    }
}