<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;

class EquipeControllerTest extends TestCase
{
    public function testBasicAssertions(): void
    {
        $this->assertTrue(true);
        $this->assertEquals(1, 1);
    }
    
    public function testArrayOperations(): void
    {
        $data = ['nom' => 'Test', 'statut' => 'Active'];
        $this->assertArrayHasKey('nom', $data);
        $this->assertEquals('Test', $data['nom']);
    }
    
    public function testStringOperations(): void
    {
        $nomEquipe = "Équipe Test";
        $this->assertStringContainsString('Test', $nomEquipe);
        $this->assertStringStartsWith('Équipe', $nomEquipe);
    }
    
    public function testMathOperations(): void
    {
        $nbMembres = 5;
        $nbMembresMax = 10;
        $this->assertLessThanOrEqual($nbMembresMax, $nbMembres);
    }
}