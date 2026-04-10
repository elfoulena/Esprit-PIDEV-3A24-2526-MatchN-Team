<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DemandeEquipeRepository;

#[ORM\Entity(repositoryClass: DemandeEquipeRepository::class)]
#[ORM\Table(name: 'demande_equipe')]
class DemandeEquipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_demande = null;

    public function getId_demande(): ?int
    {
        return $this->id_demande;
    }

    public function setId_demande(int $id_demande): self
    {
        $this->id_demande = $id_demande;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'demandeEquipes')]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id_equipe')]
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'demandeEquipes')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
    private ?User $User = null;

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_demande = null;

    public function getDate_demande(): ?\DateTimeInterface
    {
        return $this->date_demande;
    }

    public function setDate_demande(?\DateTimeInterface $date_demande): self
    {
        $this->date_demande = $date_demande;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_traitement = null;

    public function getDate_traitement(): ?\DateTimeInterface
    {
        return $this->date_traitement;
    }

    public function setDate_traitement(?\DateTimeInterface $date_traitement): self
    {
        $this->date_traitement = $date_traitement;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'demandesTraitees')]
    #[ORM\JoinColumn(name: 'traite_par', referencedColumnName: 'id')]
    private ?User $traitePar = null;

    public function getTraitePar(): ?User
    {
        return $this->traitePar;
    }

    public function setTraitePar(?User $traitePar): self
    {
        $this->traitePar = $traitePar;
        return $this;
    }

    public function getIdDemande(): ?int
    {
        return $this->id_demande;
    }

    public function getDateDemande(): ?\DateTime
    {
        return $this->date_demande;
    }

    public function setDateDemande(?\DateTime $date_demande): static
    {
        $this->date_demande = $date_demande;

        return $this;
    }

    public function getDateTraitement(): ?\DateTime
    {
        return $this->date_traitement;
    }

    public function setDateTraitement(?\DateTime $date_traitement): static
    {
        $this->date_traitement = $date_traitement;

        return $this;
    }

}
