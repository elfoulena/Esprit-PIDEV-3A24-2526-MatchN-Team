<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DemandeParticipationRepository;

#[ORM\Entity(repositoryClass: DemandeParticipationRepository::class)]
#[ORM\Table(name: 'demande_participation')]
class DemandeParticipation
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

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'demandeParticipations')]
    #[ORM\JoinColumn(name: 'id_projet', referencedColumnName: 'id_projet')]
    private ?Projet $projet = null;

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $email_freelancer = null;

    public function getEmail_freelancer(): ?string
    {
        return $this->email_freelancer;
    }

    public function setEmail_freelancer(string $email_freelancer): self
    {
        $this->email_freelancer = $email_freelancer;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_freelancer = null;

    public function getNom_freelancer(): ?string
    {
        return $this->nom_freelancer;
    }

    public function setNom_freelancer(string $nom_freelancer): self
    {
        $this->nom_freelancer = $nom_freelancer;
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

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getIdDemande(): ?int
    {
        return $this->id_demande;
    }

    public function getEmailFreelancer(): ?string
    {
        return $this->email_freelancer;
    }

    public function setEmailFreelancer(string $email_freelancer): static
    {
        $this->email_freelancer = $email_freelancer;

        return $this;
    }

    public function getNomFreelancer(): ?string
    {
        return $this->nom_freelancer;
    }

    public function setNomFreelancer(string $nom_freelancer): static
    {
        $this->nom_freelancer = $nom_freelancer;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $github = null;

    public function getGithub(): ?string
    {
        return $this->github;
    }

    public function setGithub(?string $github): static
    {
        $this->github = $github;

        return $this;
    }

}
