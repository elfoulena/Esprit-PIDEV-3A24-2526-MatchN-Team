<?php

namespace App\Tests\Entity;

use App\Entity\Evenement;
use PHPUnit\Framework\TestCase;

class EvenementTest extends TestCase
{
    public function testComputeStatutPrevu(): void
    {
        $evenement = new Evenement();
        $evenement->setDateDebut(new \DateTime('+1 day'));
        $evenement->setDateFin(new \DateTime('+2 days'));
        
        $evenement->computeStatut();
        
        $this->assertEquals('prevu', $evenement->getStatut());
    }

    public function testComputeStatutEnCours(): void
    {
        $evenement = new Evenement();
        // Since computeStatut uses 'now', we set debut in the past and fin in the future
        $evenement->setDateDebut(new \DateTime('-1 day'));
        $evenement->setDateFin(new \DateTime('+1 day'));
        
        $evenement->computeStatut();
        
        $this->assertEquals('en_cours', $evenement->getStatut());
    }

    public function testComputeStatutTermine(): void
    {
        $evenement = new Evenement();
        $evenement->setDateDebut(new \DateTime('-2 days'));
        $evenement->setDateFin(new \DateTime('-1 day'));
        
        $evenement->computeStatut();
        
        $this->assertEquals('termine', $evenement->getStatut());
    }

    public function testSettersAndGetters(): void
    {
        $evenement = new Evenement();
        $evenement->setTitre('Test Event');
        $evenement->setCapaciteMax(100);
        $evenement->setLieu('Tunis');

        $this->assertEquals('Test Event', $evenement->getTitre());
        $this->assertEquals(100, $evenement->getCapaciteMax());
        $this->assertEquals('Tunis', $evenement->getLieu());
    }
}
