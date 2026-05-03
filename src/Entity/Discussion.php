<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\DiscussionRepository;

#[ORM\Entity(repositoryClass: DiscussionRepository::class)]
#[ORM\Table(name: 'discussion')]
class Discussion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_discussion = null;

    public function getId_discussion(): ?int
    {
        return $this->id_discussion;
    }

    public function setId_discussion(int $id_discussion): self
    {
        $this->id_discussion = $id_discussion;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $id_freelancer = null;

    public function getId_freelancer(): ?int
    {
        return $this->id_freelancer;
    }

    public function setId_freelancer(int $id_freelancer): self
    {
        $this->id_freelancer = $id_freelancer;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_creation = null;

    public function getDate_creation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    /** @var Collection<int, MessageDiscussion> */
    #[ORM\OneToMany(targetEntity: MessageDiscussion::class, mappedBy: 'discussion')]
    private Collection $messageDiscussions;

    public function __construct()
    {
        $this->messageDiscussions = new ArrayCollection();
    }

    /**
     * @return Collection<int, MessageDiscussion>
     */
    public function getMessageDiscussions(): Collection
    {
        return $this->messageDiscussions;
    }

    public function addMessageDiscussion(MessageDiscussion $messageDiscussion): self
    {
        if (!$this->messageDiscussions->contains($messageDiscussion)) {
            $this->messageDiscussions->add($messageDiscussion);
        }
        return $this;
    }

    public function removeMessageDiscussion(MessageDiscussion $messageDiscussion): self
    {
        $this->messageDiscussions->removeElement($messageDiscussion);
        return $this;
    }

    public function getIdDiscussion(): ?int
    {
        return $this->id_discussion;
    }

    public function getIdFreelancer(): ?int
    {
        return $this->id_freelancer;
    }

    public function setIdFreelancer(int $id_freelancer): static
    {
        $this->id_freelancer = $id_freelancer;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

}