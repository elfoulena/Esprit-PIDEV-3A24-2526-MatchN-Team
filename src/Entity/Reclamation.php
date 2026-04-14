<?php
namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: 'reclamation')]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_reclamation')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_user', nullable: true)]   // ← corrigé
    private ?int $utilisateurId = null;

    #[ORM\Column(name: 'type_user', length: 255)]    // ← corrigé
    private ?string $type = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = 'nouveau';

    #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: ReponseReclamation::class)]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getUtilisateurId(): ?int { return $this->utilisateurId; }
    public function setUtilisateurId(?int $id): self { $this->utilisateurId = $id; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }
    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $date): self { $this->dateCreation = $date; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }
    public function getReponses(): Collection { return $this->reponses; }
}