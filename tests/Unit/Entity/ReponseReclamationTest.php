<?php
namespace App\Tests\Unit\Entity;

use App\Entity\ReponseReclamation;
use PHPUnit\Framework\TestCase;

class ReponseReclamationTest extends TestCase
{
    public function testSetMessage(): void
    {
        $r = new ReponseReclamation();
        $r->setMessage('Votre problème est résolu.');
        $this->assertEquals('Votre problème est résolu.', $r->getMessage());
    }

    public function testGetContenuRetourneMemeChoseQueMessage(): void
    {
        $r = new ReponseReclamation();
        $r->setMessage('Test contenu');
        $this->assertEquals('Test contenu', $r->getContenu());
    }

    public function testDateReponseInitialiseeAutomatiquement(): void
    {
        $r = new ReponseReclamation();
        $this->assertInstanceOf(\DateTimeInterface::class, $r->getDateReponse());
    }

    public function testSetContenu(): void
    {
        $r = new ReponseReclamation();
        $r->setContenu('Contenu via setContenu');
        $this->assertEquals('Contenu via setContenu', $r->getMessage());
    }
}
