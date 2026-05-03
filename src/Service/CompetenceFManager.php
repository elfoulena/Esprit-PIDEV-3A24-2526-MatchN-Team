<?php

namespace App\Service;

use App\Entity\CompetenceF;

class CompetenceFManager
{
    public function validate(CompetenceF $competence): bool
    {
        if (empty(trim((string) $competence->getNom()))) {
            throw new \InvalidArgumentException('Le nom de la compétence est obligatoire.');
        }

        return true;
    }

    public function descriptionDoitEtreRegeneree(string $ancienNom, CompetenceF $competence): bool
    {
        $nomAChange = $ancienNom !== $competence->getNom();
        $descriptionVide = empty(trim((string) $competence->getDescription()));

        return $nomAChange || $descriptionVide;
    }
}