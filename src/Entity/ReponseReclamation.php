<?php
namespace App\Entity;

use App\Repository\ReponseReclamationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseReclamationRepository::class)]
#[ORM\Table(name: 'reponse_reclamation')]
class ReponseReclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_reponse')]
    private ?int $id = null;

    #[ORM\Column(name: 'id_user', nullable: true)]
    private ?int $utilisateurId = null;

    #[ORM\Column(type: 'text', nullable: true)]                          // ✅
    private ?string $message = null;

    #[ORM\Column(name: 'date_reponse', type: 'datetime', nullable: true)] // ✅
    private ?\DateTimeInterface $dateReponse = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'reponses')]
    #[ORM\JoinColumn(name: 'id_reclamation', referencedColumnName: 'id_reclamation')]
    private ?Reclamation $reclamation = null;

    public function __construct()
    {
        $this->dateReponse = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getUtilisateurId(): ?int { return $this->utilisateurId; }
    public function setUtilisateurId(?int $id): self { $this->utilisateurId = $id; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }
    public function getDateReponse(): ?\DateTimeInterface { return $this->dateReponse; }
    public function getReclamation(): ?Reclamation { return $this->reclamation; }
    public function setReclamation(?Reclamation $r): self { $this->reclamation = $r; return $this; }
    public function getContenu(): ?string { return $this->message; }
    public function setContenu(string $contenu): self { return $this->setMessage($contenu); }
}