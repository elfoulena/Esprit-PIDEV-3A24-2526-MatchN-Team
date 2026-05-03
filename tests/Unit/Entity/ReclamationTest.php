<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Reclamation;
use PHPUnit\Framework\TestCase;

class ReclamationTest extends TestCase
{
    public function testStatutParDefautEstNouveau(): void
    {
        $r = new Reclamation();
        $this->assertEquals('nouveau', $r->getStatut());
    }

    public function testSetStatut(): void
    {
        $r = new Reclamation();
        $r->setStatut('en_cours');
        $this->assertEquals('en_cours', $r->getStatut());
    }

    public function testSetMessage(): void
    {
        $r = new Reclamation();
        $r->setMessage('Mon problème');
        $this->assertEquals('Mon problème', $r->getMessage());
    }

    public function testSetType(): void
    {
        $r = new Reclamation();
        $r->setType('freelancer');
        $this->assertEquals('freelancer', $r->getType());
    }

    public function testDateCreationInitialiseeAutomatiquement(): void
    {
        $r = new Reclamation();
        $this->assertInstanceOf(\DateTimeInterface::class, $r->getDateCreation());
    }

    public function testReponsesVideesParDefaut(): void
    {
        $r = new Reclamation();
        $this->assertCount(0, $r->getReponses());
    }
}