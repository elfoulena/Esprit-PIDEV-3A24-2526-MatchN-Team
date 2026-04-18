<?php

namespace App\Entity;

use App\Repository\MembreEquipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembreEquipeRepository::class)]
#[ORM\Table(name: 'membre_equipe')]
class MembreEquipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_membre', type: Types::INTEGER)]
    private ?int $idMembre = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id_equipe', nullable: false)]
    private ?Equipe $equipe = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(name: 'role_equipe', type: Types::STRING, length: 30)]
    private ?string $roleEquipe = 'Membre';

    #[ORM\Column(name: 'date_affectation', type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateAffectation = null;

    #[ORM\Column(name: 'date_fin', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'taux_participation', type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $tauxParticipation = '100.00';

    #[ORM\Column(name: 'statut_membre', type: Types::STRING, length: 20)]
    private ?string $statutMembre = 'Actif';

    #[ORM\Column(name: 'competences_principales', type: Types::STRING, length: 255, nullable: true)]
    private ?string $competencesPrincipales = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->dateAffectation = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters and setters...
    public function getIdMembre(): ?int { return $this->idMembre; }
    public function getEquipe(): ?Equipe { return $this->equipe; }
    public function setEquipe(?Equipe $equipe): self { $this->equipe = $equipe; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getRoleEquipe(): ?string { return $this->roleEquipe; }
    public function setRoleEquipe(?string $roleEquipe): self { $this->roleEquipe = $roleEquipe; return $this; }
    public function getDateAffectation(): ?\DateTimeInterface { return $this->dateAffectation; }
    public function setDateAffectation(?\DateTimeInterface $dateAffectation): self { $this->dateAffectation = $dateAffectation; return $this; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $dateFin): self { $this->dateFin = $dateFin; return $this; }
    public function getTauxParticipation(): string { return $this->tauxParticipation; }
    public function setTauxParticipation(string $tauxParticipation): self { $this->tauxParticipation = $tauxParticipation; return $this; }
    public function getStatutMembre(): ?string { return $this->statutMembre; }
    public function setStatutMembre(?string $statutMembre): self { $this->statutMembre = $statutMembre; return $this; }
    public function getCompetencesPrincipales(): ?string { return $this->competencesPrincipales; }
    public function setCompetencesPrincipales(?string $competencesPrincipales): self { $this->competencesPrincipales = $competencesPrincipales; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}