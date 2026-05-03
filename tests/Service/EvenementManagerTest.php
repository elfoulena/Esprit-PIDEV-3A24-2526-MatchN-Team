<?php

namespace App\Tests\Service;

use App\Entity\Evenement;
use App\Service\EvenementManager;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class EvenementManagerTest extends TestCase
{
    /**
     * Teste qu'un événement correct est bien validé.
     */
    public function testValidEvenement(): void
    {
        $evenement = new Evenement();
        $evenement->setTitre('Atelier Symfony');
        $evenement->setCapacite_max(50);
        $evenement->setDateDebut(new \DateTime('+1 day'));
        $evenement->setDateFin(new \DateTime('+2 days'));

        $manager = new EvenementManager();

        $this->assertTrue($manager->validate($evenement));
    }

    /**
     * Teste qu'un événement sans titre lève une exception.
     */
    public function testEvenementWithoutTitre(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre est obligatoire');

        $evenement = new Evenement();
        $evenement->setCapacite_max(50);

        $manager = new EvenementManager();
        $manager->validate($evenement);
    }

    /**
     * Teste qu'une capacité négative ou nulle lève une exception.
     */
    public function testEvenementWithInvalidCapacite(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La capacité doit être un nombre positif');

        $evenement = new Evenement();
        $evenement->setTitre('Lancement Projet');
        $evenement->setCapacite_max(0);

        $manager = new EvenementManager();
        $manager->validate($evenement);
    }

    /**
     * Teste que des dates incohérentes lèvent une exception.
     */
    public function testEvenementWithInvalidDates(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de début doit être avant la date de fin');

        $evenement = new Evenement();
        $evenement->setTitre('Conférence');
        $evenement->setCapacite_max(10);
        $evenement->setDateDebut(new \DateTime('+2 days'));
        $evenement->setDateFin(new \DateTime('+1 day')); // Fin AVANT début

        $manager = new EvenementManager();
        $manager->validate($evenement);
    }
}
