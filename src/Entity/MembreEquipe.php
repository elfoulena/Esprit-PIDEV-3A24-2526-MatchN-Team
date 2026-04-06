<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MembreEquipeRepository;

#[ORM\Entity(repositoryClass: MembreEquipeRepository::class)]
#[ORM\Table(name: 'membre_equipe')]
class MembreEquipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_membre = null;

    public function getId_membre(): ?int
    {
        return $this->id_membre;
    }

    public function setId_membre(int $id_membre): self
    {
        $this->id_membre = $id_membre;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Equipe::class, inversedBy: 'membreEquipe')]
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

    #[ORM\OneToOne(targetEntity: Utilisateur::class, inversedBy: 'membreEquipe')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', unique: true)]
    private ?Utilisateur $utilisateur = null;

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $role_equipe = null;

    public function getRole_equipe(): ?string
    {
        return $this->role_equipe;
    }

    public function setRole_equipe(?string $role_equipe): self
    {
        $this->role_equipe = $role_equipe;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_affectation = null;

    public function getDate_affectation(): ?\DateTimeInterface
    {
        return $this->date_affectation;
    }

    public function setDate_affectation(\DateTimeInterface $date_affectation): self
    {
        $this->date_affectation = $date_affectation;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_fin = null;

    public function getDate_fin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDate_fin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $taux_participation = null;

    public function getTaux_participation(): ?float
    {
        return $this->taux_participation;
    }

    public function setTaux_participation(?float $taux_participation): self
    {
        $this->taux_participation = $taux_participation;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut_membre = null;

    public function getStatut_membre(): ?string
    {
        return $this->statut_membre;
    }

    public function setStatut_membre(?string $statut_membre): self
    {
        $this->statut_membre = $statut_membre;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $competences_principales = null;

    public function getCompetences_principales(): ?string
    {
        return $this->competences_principales;
    }

    public function setCompetences_principales(?string $competences_principales): self
    {
        $this->competences_principales = $competences_principales;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
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

    public function getIdMembre(): ?int
    {
        return $this->id_membre;
    }

    public function getRoleEquipe(): ?string
    {
        return $this->role_equipe;
    }

    public function setRoleEquipe(?string $role_equipe): static
    {
        $this->role_equipe = $role_equipe;

        return $this;
    }

    public function getDateAffectation(): ?\DateTime
    {
        return $this->date_affectation;
    }

    public function setDateAffectation(\DateTime $date_affectation): static
    {
        $this->date_affectation = $date_affectation;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getTauxParticipation(): ?string
    {
        return $this->taux_participation;
    }

    public function setTauxParticipation(?string $taux_participation): static
    {
        $this->taux_participation = $taux_participation;

        return $this;
    }

    public function getStatutMembre(): ?string
    {
        return $this->statut_membre;
    }

    public function setStatutMembre(?string $statut_membre): static
    {
        $this->statut_membre = $statut_membre;

        return $this;
    }

    public function getCompetencesPrincipales(): ?string
    {
        return $this->competences_principales;
    }

    public function setCompetencesPrincipales(?string $competences_principales): static
    {
        $this->competences_principales = $competences_principales;

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
