<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CommitHistoryRepository;

#[ORM\Entity(repositoryClass: CommitHistoryRepository::class)]
#[ORM\Table(name: 'commit_history')]
class CommitHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_commit = null;

    public function getId_commit(): ?int
    {
        return $this->id_commit;
    }

    public function setId_commit(int $id_commit): self
    {
        $this->id_commit = $id_commit;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Repository::class, inversedBy: 'commitHistory')]
    #[ORM\JoinColumn(name: 'id_repo', referencedColumnName: 'id_repo', unique: true)]
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commitHistorys')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $commit_sha = null;

    public function getCommit_sha(): ?string
    {
        return $this->commit_sha;
    }

    public function setCommit_sha(string $commit_sha): self
    {
        $this->commit_sha = $commit_sha;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $branch_name = null;

    public function getBranch_name(): ?string
    {
        return $this->branch_name;
    }

    public function setBranch_name(string $branch_name): self
    {
        $this->branch_name = $branch_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
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
    private ?\DateTimeInterface $date_commit = null;

    public function getDate_commit(): ?\DateTimeInterface
    {
        return $this->date_commit;
    }

    public function setDate_commit(\DateTimeInterface $date_commit): self
    {
        $this->date_commit = $date_commit;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $score = null;

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getIdCommit(): ?int
    {
        return $this->id_commit;
    }

    public function getCommitSha(): ?string
    {
        return $this->commit_sha;
    }

    public function setCommitSha(string $commit_sha): static
    {
        $this->commit_sha = $commit_sha;

        return $this;
    }

    public function getBranchName(): ?string
    {
        return $this->branch_name;
    }

    public function setBranchName(string $branch_name): static
    {
        $this->branch_name = $branch_name;

        return $this;
    }

    public function getDateCommit(): ?\DateTime
    {
        return $this->date_commit;
    }

    public function setDateCommit(\DateTime $date_commit): static
    {
        $this->date_commit = $date_commit;

        return $this;
    }

}
