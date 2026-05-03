<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MessageDiscussionRepository;

#[ORM\Entity(repositoryClass: MessageDiscussionRepository::class)]
#[ORM\Table(name: 'message_discussion')]
class MessageDiscussion
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

    #[ORM\ManyToOne(targetEntity: Discussion::class, inversedBy: 'messageDiscussions')]
    #[ORM\JoinColumn(name: 'id_discussion', referencedColumnName: 'id_discussion')]
    private ?Discussion $discussion = null;

    public function getDiscussion(): ?Discussion
    {
        return $this->discussion;
    }

    public function setDiscussion(?Discussion $discussion): self
    {
        $this->discussion = $discussion;
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
    private ?string $role_expediteur = null;

    public function getRole_expediteur(): ?string
    {
        return $this->role_expediteur;
    }

    public function setRole_expediteur(string $role_expediteur): self
    {
        $this->role_expediteur = $role_expediteur;
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

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_envoi = null;

    public function getDate_envoi(): ?\DateTimeInterface
    {
        return $this->date_envoi;
    }

    public function setDate_envoi(\DateTimeInterface $date_envoi): self
    {
        $this->date_envoi = $date_envoi;
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

    public function getRoleExpediteur(): ?string
    {
        return $this->role_expediteur;
    }

    public function setRoleExpediteur(string $role_expediteur): static
    {
        $this->role_expediteur = $role_expediteur;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->date_envoi;
    }

    public function setDateEnvoi(\DateTimeInterface $date_envoi): static
    {
        $this->date_envoi = $date_envoi;

        return $this;
    }

}
