<?php

namespace App\Tests\Unit\Entity;

use App\Entity\EvaluationPartTime;
use PHPUnit\Framework\TestCase;

class EvaluationPartTimeTest extends TestCase
{
    public function testGetLabelNoteAvecNoteNullRetourneNonNote(): void
    {
        $evaluation = new EvaluationPartTime();

        $this->assertSame('Non noté', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote10RetourneExcellent(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(10);

        $this->assertSame('Excellent', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote9RetourneExcellent(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(9);

        $this->assertSame('Excellent', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote8RetourneBien(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(8);

        $this->assertSame('Bien', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote6RetourneMoyen(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(6);

        $this->assertSame('Moyen', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote4RetourneInsuffisant(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(4);

        $this->assertSame('Insuffisant', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote2RetourneTresInsuffisant(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(2);

        $this->assertSame('Très insuffisant', $evaluation->getLabelNote());
    }

    public function testGetLabelNoteAvecNote0RetourneTresInsuffisant(): void
    {
        $evaluation = new EvaluationPartTime();
        $evaluation->setNote(0);

        $this->assertSame('Très insuffisant', $evaluation->getLabelNote());
    }
}
