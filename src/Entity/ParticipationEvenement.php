<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ParticipationEvenementRepository;

#[ORM\Entity(repositoryClass: ParticipationEvenementRepository::class)]
#[ORM\Table(name: 'participation_evenement')]
class ParticipationEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_participation = null;

    public function getId_participation(): ?int
    {
        return $this->id_participation;
    }

    public function setId_participation(int $id_participation): self
    {
        $this->id_participation = $id_participation;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(name: 'id_evenement', referencedColumnName: 'id_evenement', nullable: false)]
    private ?Evenement $evenement = null;

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(name: 'id_User', referencedColumnName: 'id', nullable: false)]
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

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $presence = null;

    public function isPresence(): ?bool
    {
        return $this->presence;
    }

    public function setPresence(?bool $presence): self
    {
        $this->presence = $presence;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_inscription = null;

    public function getDate_inscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDate_inscription(?\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $token = null;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $jeton = null;

    public function getJeton(): ?string
    {
        return $this->jeton;
    }

    public function setJeton(?string $jeton): self
    {
        $this->jeton = $jeton;
        return $this;
    }

    public function getIdParticipation(): ?int
    {
        return $this->id_participation;
    }

    public function getDateInscription(): ?\DateTime
    {
        return $this->date_inscription;
    }

    public function setDateInscription(?\DateTime $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

}
