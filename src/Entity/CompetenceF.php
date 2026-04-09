<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CompetenceFRepository;

#[ORM\Entity(repositoryClass: CompetenceFRepository::class)]
#[ORM\Table(name: 'competence_f')]
class CompetenceF
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'competenceFs')]
    #[ORM\JoinTable(
        name: 'freelancer_competence',
        joinColumns: [
            new ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'freelancer_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $Users;

    public function __construct()
    {
        $this->Users = new ArrayCollection();
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        if (!$this->Users instanceof Collection) {
            $this->Users = new ArrayCollection();
        }
        return $this->Users;
    }

    public function addUser(User $User): self
    {
        if (!$this->getUsers()->contains($User)) {
            $this->getUsers()->add($User);
        }
        return $this;
    }

    public function removeUser(User $User): self
    {
        $this->getUsers()->removeElement($User);
        return $this;
    }

}
