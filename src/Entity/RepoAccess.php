<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RepoAccessRepository;

#[ORM\Entity(repositoryClass: RepoAccessRepository::class)]
#[ORM\Table(name: 'repo_access')]
class RepoAccess
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Repository::class, inversedBy: 'repoAccesss')]
    #[ORM\JoinColumn(name: 'id_repo', referencedColumnName: 'id_repo')]
    private ?Repository $repository = null;

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): self
    {
        $this->repository = $repository;
        return $this;
    }

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'repoAccesss')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
    private ?Utilisateur $utilisateur = null;

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $permission = null;

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $invite_status = null;

    public function getInvite_status(): ?string
    {
        return $this->invite_status;
    }

    public function setInvite_status(string $invite_status): self
    {
        $this->invite_status = $invite_status;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $invited_at = null;

    public function getInvited_at(): ?\DateTimeInterface
    {
        return $this->invited_at;
    }

    public function setInvited_at(\DateTimeInterface $invited_at): self
    {
        $this->invited_at = $invited_at;
        return $this;
    }

    public function getInviteStatus(): ?string
    {
        return $this->invite_status;
    }

    public function setInviteStatus(string $invite_status): static
    {
        $this->invite_status = $invite_status;

        return $this;
    }

    public function getInvitedAt(): ?\DateTime
    {
        return $this->invited_at;
    }

    public function setInvitedAt(\DateTime $invited_at): static
    {
        $this->invited_at = $invited_at;

        return $this;
    }

}
