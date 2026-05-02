<?php

namespace App\Service;

use App\Entity\Evenement;
use InvalidArgumentException;

class EvenementManager
{
    public function validate(Evenement $evenement): bool
    {
        // 1. Le titre est obligatoire
        if (empty($evenement->getTitre())) {
            throw new InvalidArgumentException('Le titre est obligatoire');
        }

        // 2. La capacité doit être positive et au moins de 1
        if ($evenement->getCapacite_max() <= 0) {
            throw new InvalidArgumentException('La capacité doit être un nombre positif');
        }

        // 3. La date de début doit être avant la date de fin
        if ($evenement->getDate_debut() !== null && $evenement->getDate_fin() !== null) {
            if ($evenement->getDate_debut() >= $evenement->getDate_fin()) {
                throw new InvalidArgumentException('La date de début doit être avant la date de fin');
            }
        }

        // 4. La deadline doit être avant ou égale à la date de début
        if ($evenement->getDate_deadline() !== null && $evenement->getDate_debut() !== null) {
            if ($evenement->getDate_deadline() > $evenement->getDate_debut()) {
                throw new InvalidArgumentException('La deadline doit être avant ou égale à la date de début');
            }
        }

        return true;
    }
}
