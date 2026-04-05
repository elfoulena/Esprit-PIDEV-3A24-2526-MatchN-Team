<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MessageRepository;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_message = null;

    public function getId_message(): ?int
    {
        return $this->id_message;
    }

    public function setId_message(int $id_message): self
    {
        $this->id_message = $id_message;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'messages')]
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $id_expediteur = null;

    public function getId_expediteur(): ?int
    {
        return $this->id_expediteur;
    }

    public function setId_expediteur(int $id_expediteur): self
    {
        $this->id_expediteur = $id_expediteur;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_expediteur = null;

    public function getNom_expediteur(): ?string
    {
        return $this->nom_expediteur;
    }

    public function setNom_expediteur(string $nom_expediteur): self
    {
        $this->nom_expediteur = $nom_expediteur;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $contenu = null;

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_envoi = null;

    public function getDate_envoi(): ?\DateTimeInterface
    {
        return $this->date_envoi;
    }

    public function setDate_envoi(?\DateTimeInterface $date_envoi): self
    {
        $this->date_envoi = $date_envoi;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $est_supprime = null;

    public function isEst_supprime(): ?bool
    {
        return $this->est_supprime;
    }

    public function setEst_supprime(?bool $est_supprime): self
    {
        $this->est_supprime = $est_supprime;
        return $this;
    }

    public function getIdMessage(): ?int
    {
        return $this->id_message;
    }

    public function getIdExpediteur(): ?int
    {
        return $this->id_expediteur;
    }

    public function setIdExpediteur(int $id_expediteur): static
    {
        $this->id_expediteur = $id_expediteur;

        return $this;
    }

    public function getNomExpediteur(): ?string
    {
        return $this->nom_expediteur;
    }

    public function setNomExpediteur(string $nom_expediteur): static
    {
        $this->nom_expediteur = $nom_expediteur;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTime
    {
        return $this->date_envoi;
    }

    public function setDateEnvoi(?\DateTime $date_envoi): static
    {
        $this->date_envoi = $date_envoi;

        return $this;
    }

    public function isEstSupprime(): ?bool
    {
        return $this->est_supprime;
    }

    public function setEstSupprime(?bool $est_supprime): static
    {
        $this->est_supprime = $est_supprime;

        return $this;
    }

}
