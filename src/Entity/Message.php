<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_message', type: Types::INTEGER)]
    private ?int $idMessage = null;

    #[ORM\Column(name: 'id_expediteur', type: Types::INTEGER)]
    private ?int $idExpediteur = null;

    #[ORM\Column(name: 'nom_expediteur', type: Types::STRING, length: 255)]
    private ?string $nomExpediteur = null;

    #[ORM\Column(name: 'contenu', type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(name: 'date_envoi', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\Column(name: 'est_supprime', type: Types::BOOLEAN, nullable: true)]
    private ?bool $estSupprime = false;

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id_equipe', nullable: true)]
    private ?Equipe $equipe = null;

    public function __construct()
    {
        $this->dateEnvoi = new \DateTime();
        $this->estSupprime = false;
    }

    // Getters and Setters
    public function getIdMessage(): ?int { return $this->idMessage; }
    
    public function getIdExpediteur(): ?int { return $this->idExpediteur; }
    public function setIdExpediteur(int $idExpediteur): self { $this->idExpediteur = $idExpediteur; return $this; }
    
    public function getNomExpediteur(): ?string { return $this->nomExpediteur; }
    public function setNomExpediteur(string $nomExpediteur): self { $this->nomExpediteur = $nomExpediteur; return $this; }
    
    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(string $contenu): self { $this->contenu = $contenu; return $this; }
    
    public function getDateEnvoi(): ?\DateTimeInterface { return $this->dateEnvoi; }
    public function setDateEnvoi(?\DateTimeInterface $dateEnvoi): self { $this->dateEnvoi = $dateEnvoi; return $this; }
    
    public function isEstSupprime(): ?bool { return $this->estSupprime; }
    public function setEstSupprime(?bool $estSupprime): self { $this->estSupprime = $estSupprime; return $this; }
    
    public function getEquipe(): ?Equipe { return $this->equipe; }
    public function setEquipe(?Equipe $equipe): self { $this->equipe = $equipe; return $this; }
}