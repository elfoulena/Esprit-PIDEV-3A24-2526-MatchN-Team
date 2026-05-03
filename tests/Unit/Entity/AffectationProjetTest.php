<?php

namespace App\Tests\Unit\Entity;

use App\Entity\AffectationProjet;
use PHPUnit\Framework\TestCase;

class AffectationProjetTest extends TestCase
{
    public function testPeutEtreEvalueeAvecStatutTerminee(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setStatut('TERMINEE');

        $this->assertTrue($affectation->peutEtreEvaluee());
    }

    public function testPeutEtreEvalueeAvecStatutAcceptee(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setStatut('ACCEPTEE');

        $this->assertTrue($affectation->peutEtreEvaluee());
    }

    public function testPeutEtreEvalueeAvecStatutEnAttente(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setStatut('EN_ATTENTE');

        $this->assertFalse($affectation->peutEtreEvaluee());
    }

    public function testPeutEtreEvalueeAvecStatutRefusee(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setStatut('REFUSEE');

        $this->assertFalse($affectation->peutEtreEvaluee());
    }

    public function testPeutEtreEvalueeAvecStatutNull(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setStatut(null);

        $this->assertFalse($affectation->peutEtreEvaluee());
    }

    public function testCalculerCoutEstimeRetourneNullSansDateDebut(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setDate_fin(new \DateTime('2025-01-10'));
        $affectation->setTaux_horaire(20.0);

        $this->assertNull($affectation->calculerCoutEstime());
    }

    public function testCalculerCoutEstimeRetourneNullSansDateFin(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setDate_debut(new \DateTime('2025-01-01'));
        $affectation->setTaux_horaire(20.0);

        $this->assertNull($affectation->calculerCoutEstime());
    }

    public function testCalculerCoutEstimeRetourneNullSansTauxHoraire(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setDate_debut(new \DateTime('2025-01-01'));
        $affectation->setDate_fin(new \DateTime('2025-01-10'));

        $this->assertNull($affectation->calculerCoutEstime());
    }

    public function testCalculerCoutEstimeAvecDonneesCompletes(): void
    {
        $affectation = new AffectationProjet();
        $affectation->setDate_debut(new \DateTime('2025-01-01'));
        $affectation->setDate_fin(new \DateTime('2025-01-06'));
        $affectation->setTaux_horaire(25.0);

        // 5 jours * 8h/jour * 25 DT/h = 1000.0
        $this->assertSame(1000.0, $affectation->calculerCoutEstime());
    }
}
