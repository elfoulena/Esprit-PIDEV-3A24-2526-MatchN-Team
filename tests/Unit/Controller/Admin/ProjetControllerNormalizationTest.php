<?php

namespace App\Tests\Unit\Controller\Admin;

use App\Controller\Admin\ProjetController;
use PHPUnit\Framework\TestCase;

class ProjetControllerNormalizationTest extends TestCase
{
    public function testNormalizeAiProjectDataCleansAndNormalizesProjectFields(): void
    {
        $controller = new ProjetController();

        $raw = [
            'titre' => '  Migration ERP  ',
            'description' => '  Projet de migration  ',
            'statut' => 'en_cours',
            'priorite' => 'critical',
            'dateDebut' => '2026-05-20',
            'dateFin' => '2026-05-01',
            'dateLivraison' => '2026-04-30',
            'budgetTotal' => '100.556',
            'budgetInterne' => '40.331',
            'budgetFreelance' => '-1',
            'visibleEmploye' => 'true',
            'visibleFreelancer' => '0',
            'competenceKeywords' => ['  PHP ', '', 'PHP', 'Symfony', 'Twig', 'Docker', 'SQL', 'Git', 'Tests', 'CI', 'CD', 'API'],
        ];

        $normalized = $this->invokeNormalizeAiProjectData($controller, $raw);

        self::assertSame('Migration ERP', $normalized['titre']);
        self::assertSame('Projet de migration', $normalized['description']);
        self::assertSame('EN_COURS', $normalized['statut']);
        self::assertArrayNotHasKey('priorite', $normalized);
        self::assertSame('2026-05-20', $normalized['dateDebut']);
        self::assertSame('2026-05-20', $normalized['dateFin']);
        self::assertSame('2026-05-20', $normalized['dateLivraison']);
        self::assertSame(100.56, $normalized['budgetTotal']);
        self::assertSame(40.33, $normalized['budgetInterne']);
        self::assertArrayNotHasKey('budgetFreelance', $normalized);
        self::assertTrue($normalized['visibleEmploye']);
        self::assertFalse($normalized['visibleFreelancer']);

        self::assertCount(10, $normalized['competenceKeywords']);
        self::assertSame('PHP', $normalized['competenceKeywords'][0]);
        self::assertSame('API', $normalized['competenceKeywords'][9]);
    }

    public function testNormalizeAiProjectDataRejectsInvalidDateAndNumbers(): void
    {
        $controller = new ProjetController();

        $raw = [
            'dateDebut' => 'not-a-date',
            'budgetTotal' => 'abc',
            'budgetInterne' => null,
            'budgetFreelance' => -4,
            'visibleEmploye' => 'unexpected',
        ];

        $normalized = $this->invokeNormalizeAiProjectData($controller, $raw);

        self::assertArrayNotHasKey('dateDebut', $normalized);
        self::assertArrayNotHasKey('budgetTotal', $normalized);
        self::assertArrayNotHasKey('budgetInterne', $normalized);
        self::assertArrayNotHasKey('budgetFreelance', $normalized);
        self::assertArrayNotHasKey('visibleEmploye', $normalized);
    }

    private function invokeNormalizeAiProjectData(ProjetController $controller, array $raw): array
    {
        $method = new \ReflectionMethod(ProjetController::class, 'normalizeAiProjectData');
        $method->setAccessible(true);

        /** @var array $result */
        $result = $method->invoke($controller, $raw);

        return $result;
    }
}

