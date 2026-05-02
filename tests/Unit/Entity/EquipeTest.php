<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Equipe;
use App\Entity\MembreEquipe;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Equipe.
 *
 * Couvre les règles métier suivantes :
 *  - Valeurs par défaut à la création d'une équipe (statut, nb membres, couleur)
 *  - Initialisation automatique des dates (dateCreation, createdAt, updatedAt)
 *  - Logique de gestion de la relation OneToMany avec MembreEquipe
 *    (ajout, idempotence, suppression, dissociation)
 */
class EquipeTest extends TestCase
{
    /**
     * Règle 1 — Une nouvelle équipe doit posséder un statut « Active » par défaut.
     */
    public function testStatutParDefautEstActive(): void
    {
        $equipe = new Equipe();

        $this->assertSame('Active', $equipe->getStatut());
    }

    /**
     * Règle 2 — Une nouvelle équipe doit avoir un nombre maximum de membres égal à 10 par défaut.
     */
    public function testNbMembresMaxParDefaut(): void
    {
        $equipe = new Equipe();

        $this->assertSame(10, $equipe->getNbMembresMax());
    }

    /**
     * Règle 3 — Le nombre actuel de membres doit être initialisé à 0.
     */
    public function testNbMembresActuelInitialiseAZero(): void
    {
        $equipe = new Equipe();

        $this->assertSame(0, $equipe->getNbMembresActuel());
    }

    /**
     * Règle 4 — La couleur d'équipe par défaut doit être un code hexadécimal valide (#RRGGBB).
     */
    public function testCouleurEquipeParDefautEstHexValide(): void
    {
        $equipe = new Equipe();

        $couleur = $equipe->getCouleurEquipe();
        $this->assertNotNull($couleur);
        $this->assertSame('#3498db', $couleur);
        $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $couleur);
    }

    /**
     * Règle 5 — Les dates (dateCreation, createdAt, updatedAt) doivent être initialisées
     * automatiquement par le constructeur.
     */
    public function testDateCreationInitialiseeAutomatiquement(): void
    {
        $equipe = new Equipe();

        $this->assertInstanceOf(\DateTimeInterface::class, $equipe->getDateCreation());
        $this->assertInstanceOf(\DateTimeInterface::class, $equipe->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $equipe->getUpdatedAt());
    }

    /**
     * Règle 6 — La collection de membres doit être instanciée et vide à la création.
     */
    public function testCollectionMembresInitialiseeVide(): void
    {
        $equipe = new Equipe();

        $this->assertInstanceOf(Collection::class, $equipe->getMembres());
        $this->assertCount(0, $equipe->getMembres());
    }

    /**
     * Règle 7 — L'ajout d'un membre doit l'insérer dans la collection ET
     * mettre à jour sa relation equipe.
     */
    public function testAddMembreAjouteEtAssocieEquipe(): void
    {
        $equipe = new Equipe();
        $membre = new MembreEquipe();

        $equipe->addMembre($membre);

        $this->assertCount(1, $equipe->getMembres());
        $this->assertTrue($equipe->getMembres()->contains($membre));
        $this->assertSame($equipe, $membre->getEquipe());
    }

    /**
     * Règle 8 — L'ajout d'un même membre deux fois ne doit pas le dupliquer (idempotence).
     */
    public function testAddMembreEstIdempotent(): void
    {
        $equipe = new Equipe();
        $membre = new MembreEquipe();

        $equipe->addMembre($membre);
        $equipe->addMembre($membre); // ajout en double : ne doit rien changer

        $this->assertCount(1, $equipe->getMembres());
    }

    /**
     * Règle 9 — La suppression d'un membre doit le retirer de la collection
     * et dissocier sa relation equipe (qui doit redevenir null).
     */
    public function testRemoveMembreRetireEtDissocie(): void
    {
        $equipe = new Equipe();
        $membre = new MembreEquipe();

        $equipe->addMembre($membre);
        $this->assertCount(1, $equipe->getMembres());

        $equipe->removeMembre($membre);

        $this->assertCount(0, $equipe->getMembres());
        $this->assertNull($membre->getEquipe());
    }
}
