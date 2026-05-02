<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AffectationProjet;
use App\Entity\Competence;
use App\Entity\DemandeParticipation;
use App\Entity\Projet;
use PHPUnit\Framework\TestCase;

class ProjetTest extends TestCase
{
    public function testProjetDefaultsAndScalarSetters(): void
    {
        $projet = new Projet();

        self::assertFalse($projet->isVisibleEmploye());
        self::assertFalse($projet->isVisibleFreelancer());
        self::assertCount(0, $projet->getCompetences());
        self::assertCount(0, $projet->getAffectationProjets());
        self::assertCount(0, $projet->getDemandeParticipations());

        $projet
            ->setTitre('Plateforme CRM')
            ->setDescription('Gestion des prospects et clients')
            ->setStatut('EN_COURS')
            ->setPriorite('HAUTE')
            ->setVisibleEmploye(true)
            ->setVisibleFreelancer(true)
            ->setBudgetTotal('1000.50')
            ->setBudgetInterne('600.25')
            ->setBudgetFreelance('400.25');

        self::assertSame('Plateforme CRM', $projet->getTitre());
        self::assertSame('Gestion des prospects et clients', $projet->getDescription());
        self::assertSame('EN_COURS', $projet->getStatut());
        self::assertSame('HAUTE', $projet->getPriorite());
        self::assertTrue($projet->isVisibleEmploye());
        self::assertTrue($projet->isVisibleFreelancer());
        self::assertSame('1000.50', $projet->getBudgetTotal());
        self::assertSame('600.25', $projet->getBudgetInterne());
        self::assertSame('400.25', $projet->getBudgetFreelance());
    }

    public function testDateAccessorsLegacyAndCamelCaseStayInSync(): void
    {
        $projet = new Projet();
        $debut = new \DateTime('2026-05-01');
        $fin = new \DateTime('2026-06-01');
        $livraison = new \DateTime('2026-06-10');

        $projet
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->setDateLivraison($livraison);

        self::assertSame($debut, $projet->getDate_debut());
        self::assertSame($fin, $projet->getDate_fin());
        self::assertSame($livraison, $projet->getDate_livraison());

        $debut2 = new \DateTime('2026-07-01');
        $projet->setDate_debut($debut2);
        self::assertSame($debut2, $projet->getDateDebut());
    }

    public function testCollectionsDoNotDuplicateItemsAndCanRemove(): void
    {
        $projet = new Projet();
        $competence = new Competence();
        $affectation = new AffectationProjet();
        $demande = new DemandeParticipation();

        $projet->addCompetence($competence);
        $projet->addCompetence($competence);
        self::assertCount(1, $projet->getCompetences());
        $projet->removeCompetence($competence);
        self::assertCount(0, $projet->getCompetences());

        $projet->addAffectationProjet($affectation);
        $projet->addAffectationProjet($affectation);
        self::assertCount(1, $projet->getAffectationProjets());
        $projet->removeAffectationProjet($affectation);
        self::assertCount(0, $projet->getAffectationProjets());

        $projet->addDemandeParticipation($demande);
        $projet->addDemandeParticipation($demande);
        self::assertCount(1, $projet->getDemandeParticipations());
        $projet->removeDemandeParticipation($demande);
        self::assertCount(0, $projet->getDemandeParticipations());
    }
}

