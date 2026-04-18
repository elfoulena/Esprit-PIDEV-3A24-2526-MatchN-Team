<?php

namespace App\Entity;

use App\Repository\TeamRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRequestRepository::class)]
#[ORM\Table(name: 'demande_equipe')]
class TeamRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_demande', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: false)]
    private ?User $employee = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id_equipe', nullable: false)]
    private ?Equipe $team = null;

    #[ORM\Column(name: 'statut', length: 255, nullable: true)]
    private ?string $status = 'pending';

    #[ORM\Column(name: 'message', type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(name: 'date_demande', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'date_traitement', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $processedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'traite_par', referencedColumnName: 'id', nullable: true)]
    private ?User $processedByUser = null;

    // Note: requested_role doesn't exist in your table, so we'll make it a transient property or remove it
    // For now, we'll keep it but not map to database, or we can add it to the table later
    private ?string $requestedRole = 'Membre';

    // Admin notes field - your table doesn't have this, so we'll make it transient or add to table
    private ?string $adminNotes = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = 'pending';
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getEmployee(): ?User { return $this->employee; }
    public function setEmployee(?User $employee): static { $this->employee = $employee; return $this; }

    public function getTeam(): ?Equipe { return $this->team; }
    public function setTeam(?Equipe $team): static { $this->team = $team; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): static { $this->message = $message; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getProcessedAt(): ?\DateTimeInterface { return $this->processedAt; }
    public function setProcessedAt(?\DateTimeInterface $processedAt): static { $this->processedAt = $processedAt; return $this; }

    public function getProcessedByUser(): ?User { return $this->processedByUser; }
    public function setProcessedByUser(?User $processedByUser): static { $this->processedByUser = $processedByUser; return $this; }

    // For backward compatibility with existing code
    public function getProcessedBy(): ?int 
    { 
        return $this->processedByUser ? $this->processedByUser->getId() : null; 
    }
    
    public function setProcessedBy(?int $processedBy): static 
    { 
        // This is handled by setProcessedByUser, but kept for compatibility
        return $this; 
    }

    public function getRequestedRole(): ?string { return $this->requestedRole; }
    public function setRequestedRole(?string $requestedRole): static { $this->requestedRole = $requestedRole; return $this; }

    public function getAdminNotes(): ?string { return $this->adminNotes; }
    public function setAdminNotes(?string $adminNotes): static { $this->adminNotes = $adminNotes; return $this; }
}