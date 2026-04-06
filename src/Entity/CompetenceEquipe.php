<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CompetenceEquipeRepository;

#[ORM\Entity(repositoryClass: CompetenceEquipeRepository::class)]
#[ORM\Table(name: 'competence_equipe')]
class CompetenceEquipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_competence_equipe = null;

    public function getId_competence_equipe(): ?int
    {
        return $this->id_competence_equipe;
    }

    public function setId_competence_equipe(int $id_competence_equipe): self
    {
        $this->id_competence_equipe = $id_competence_equipe;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Equipe::class, inversedBy: 'competenceEquipe')]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id_equipe', unique: true)]
    private ?Equipe $equipe = null;

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $categorie = null;

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $niveau_moyen = null;

    public function getNiveau_moyen(): ?float
    {
        return $this->niveau_moyen;
    }

    public function setNiveau_moyen(?float $niveau_moyen): self
    {
        $this->niveau_moyen = $niveau_moyen;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nb_membres_competents = null;

    public function getNb_membres_competents(): ?int
    {
        return $this->nb_membres_competents;
    }

    public function setNb_membres_competents(?int $nb_membres_competents): self
    {
        $this->nb_membres_competents = $nb_membres_competents;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $certifications = null;

    public function getCertifications(): ?string
    {
        return $this->certifications;
    }

    public function setCertifications(?string $certifications): self
    {
        $this->certifications = $certifications;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $updated_at = null;

    public function getUpdated_at(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdated_at(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getIdCompetenceEquipe(): ?int
    {
        return $this->id_competence_equipe;
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

    public function getNiveauMoyen(): ?string
    {
        return $this->niveau_moyen;
    }

    public function setNiveauMoyen(?string $niveau_moyen): static
    {
        $this->niveau_moyen = $niveau_moyen;

        return $this;
    }

    public function getNbMembresCompetents(): ?int
    {
        return $this->nb_membres_competents;
    }

    public function setNbMembresCompetents(?int $nb_membres_competents): static
    {
        $this->nb_membres_competents = $nb_membres_competents;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

}
