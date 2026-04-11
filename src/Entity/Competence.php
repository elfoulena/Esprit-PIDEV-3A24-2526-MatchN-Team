<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CompetenceRepository;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
#[ORM\Table(name: 'competence')]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_competence = null;

    public function getId_competence(): ?int
    {
        return $this->id_competence;
    }

    public function setId_competence(int $id_competence): self
    {
        $this->id_competence = $id_competence;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_competence = null;

    public function getNom_competence(): ?string
    {
        return $this->nom_competence;
    }

    public function setNom_competence(string $nom_competence): self
    {
        $this->nom_competence = $nom_competence;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description_competence = null;

    public function getDescription_competence(): ?string
    {
        return $this->description_competence;
    }

    public function setDescription_competence(?string $description_competence): self
    {
        $this->description_competence = $description_competence;
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Projet::class, mappedBy: 'competences')]
    private Collection $projets;

    public function __construct()
    {
        $this->projets = new ArrayCollection();
    }

    /**
     * @return Collection<int, Projet>
     */
    public function getProjets(): Collection
    {
        if (!$this->projets instanceof Collection) {
            $this->projets = new ArrayCollection();
        }
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->getProjets()->contains($projet)) {
            $this->getProjets()->add($projet);
        }
        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        $this->getProjets()->removeElement($projet);
        return $this;
    }

    public function getIdCompetence(): ?int
    {
        return $this->id_competence;
    }

    public function getNomCompetence(): ?string
    {
        return $this->nom_competence;
    }

    public function setNomCompetence(string $nom_competence): static
    {
        $this->nom_competence = $nom_competence;

        return $this;
    }

    public function getDescriptionCompetence(): ?string
    {
        return $this->description_competence;
    }

    public function setDescriptionCompetence(?string $description_competence): static
    {
        $this->description_competence = $description_competence;

        return $this;
    }

}
