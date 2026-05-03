<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RepositoryRepository;

#[ORM\Entity(repositoryClass: RepositoryRepository::class)]
#[ORM\Table(name: 'repository')]
class Repository
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_repo = null;

    public function getId_repo(): ?int
    {
        return $this->id_repo;
    }

    public function setId_repo(int $id_repo): self
    {
        $this->id_repo = $id_repo;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Projet::class, inversedBy: 'repository')]
    #[ORM\JoinColumn(name: 'id_projet', referencedColumnName: 'id_projet', unique: true)]
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
    private ?string $nom_repo = null;

    public function getNom_repo(): ?string
    {
        return $this->nom_repo;
    }

    public function setNom_repo(string $nom_repo): self
    {
        $this->nom_repo = $nom_repo;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $url_repo = null;

    public function getUrl_repo(): ?string
    {
        return $this->url_repo;
    }

    public function setUrl_repo(string $url_repo): self
    {
        $this->url_repo = $url_repo;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $owner = null;

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $repo_name = null;

    public function getRepo_name(): ?string
    {
        return $this->repo_name;
    }

    public function setRepo_name(string $repo_name): self
    {
        $this->repo_name = $repo_name;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $is_private = null;

    public function is_private(): ?bool
    {
        return $this->is_private;
    }

    public function setIs_private(bool $is_private): self
    {
        $this->is_private = $is_private;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: CommitHistory::class, mappedBy: 'repository')]
    private ?CommitHistory $commitHistory = null;

    public function getCommitHistory(): ?CommitHistory
    {
        return $this->commitHistory;
    }

    public function setCommitHistory(?CommitHistory $commitHistory): self
    {
        $this->commitHistory = $commitHistory;
        return $this;
    }

    /** @var Collection<int, RepoAccess> */
    #[ORM\OneToMany(targetEntity: RepoAccess::class, mappedBy: 'repository')]
    private Collection $repoAccesss;

    public function __construct()
    {
        $this->repoAccesss = new ArrayCollection();
    }

    /**
     * @return Collection<int, RepoAccess>
     */
    public function getRepoAccesss(): Collection
    {
        return $this->repoAccesss;
    }

    public function addRepoAccess(RepoAccess $repoAccess): self
    {
        if (!$this->getRepoAccesss()->contains($repoAccess)) {
            $this->getRepoAccesss()->add($repoAccess);
        }
        return $this;
    }

    public function removeRepoAccess(RepoAccess $repoAccess): self
    {
        $this->getRepoAccesss()->removeElement($repoAccess);
        return $this;
    }

    public function getIdRepo(): ?int
    {
        return $this->id_repo;
    }

    public function getNomRepo(): ?string
    {
        return $this->nom_repo;
    }

    public function setNomRepo(string $nom_repo): static
    {
        $this->nom_repo = $nom_repo;

        return $this;
    }

    public function getUrlRepo(): ?string
    {
        return $this->url_repo;
    }

    public function setUrlRepo(string $url_repo): static
    {
        $this->url_repo = $url_repo;

        return $this;
    }

    public function getRepoName(): ?string
    {
        return $this->repo_name;
    }

    public function setRepoName(string $repo_name): static
    {
        $this->repo_name = $repo_name;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->is_private;
    }

    public function setIsPrivate(bool $is_private): static
    {
        $this->is_private = $is_private;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function addRepoAccesss(RepoAccess $repoAccesss): static
    {
        if (!$this->repoAccesss->contains($repoAccesss)) {
            $this->repoAccesss->add($repoAccesss);
            $repoAccesss->setRepository($this);
        }

        return $this;
    }

    public function removeRepoAccesss(RepoAccess $repoAccesss): static
    {
        if ($this->repoAccesss->removeElement($repoAccesss)) {
            // set the owning side to null (unless already changed)
            if ($repoAccesss->getRepository() === $this) {
                $repoAccesss->setRepository(null);
            }
        }

        return $this;
    }

}
