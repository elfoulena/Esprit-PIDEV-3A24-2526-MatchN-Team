<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReponseReclamationRepository;

#[ORM\Entity(repositoryClass: ReponseReclamationRepository::class)]
#[ORM\Table(name: 'reponse_reclamation')]
class ReponseReclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_reponse = null;

    public function getId_reponse(): ?int
    {
        return $this->id_reponse;
    }

    public function setId_reponse(int $id_reponse): self
    {
        $this->id_reponse = $id_reponse;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $id_reclamation = null;

    public function getId_reclamation(): ?int
    {
        return $this->id_reclamation;
    }

    public function setId_reclamation(int $id_reclamation): self
    {
        $this->id_reclamation = $id_reclamation;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $id_utilisateur = null;

    public function getId_utilisateur(): ?int
    {
        return $this->id_utilisateur;
    }

    public function setId_utilisateur(int $id_utilisateur): self
    {
        $this->id_utilisateur = $id_utilisateur;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $message = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_reponse = null;

    public function getDate_reponse(): ?\DateTimeInterface
    {
        return $this->date_reponse;
    }

    public function setDate_reponse(\DateTimeInterface $date_reponse): self
    {
        $this->date_reponse = $date_reponse;
        return $this;
    }

    public function getIdReponse(): ?int
    {
        return $this->id_reponse;
    }

    public function getIdReclamation(): ?int
    {
        return $this->id_reclamation;
    }

    public function setIdReclamation(int $id_reclamation): static
    {
        $this->id_reclamation = $id_reclamation;

        return $this;
    }

    public function getIdUtilisateur(): ?int
    {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur(int $id_utilisateur): static
    {
        $this->id_utilisateur = $id_utilisateur;

        return $this;
    }

    public function getDateReponse(): ?\DateTime
    {
        return $this->date_reponse;
    }

    public function setDateReponse(\DateTime $date_reponse): static
    {
        $this->date_reponse = $date_reponse;

        return $this;
    }

}
