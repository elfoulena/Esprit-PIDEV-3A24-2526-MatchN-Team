<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
#[ORM\Table(name: 'equipe')]
#[ORM\HasLifecycleCallbacks]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_equipe', type: Types::INTEGER)]
    private ?int $idEquipe = null;

    #[ORM\Column(name: 'nom_equipe', type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: 'Le nom de l\'équipe est obligatoire.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Minimum 2 caractères.', maxMessage: 'Maximum 100 caractères.')]
    private ?string $nomEquipe = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'date_creation', type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date de création est obligatoire.')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::STRING, length: 20, columnDefinition: "ENUM('Active','Inactive','En pause') DEFAULT 'Active'")]
    private ?string $statut = 'Active';

    #[ORM\Column(name: 'chef_equipe_id', type: Types::INTEGER, nullable: true)]
    private ?int $chefEquipeId = null;

    #[ORM\Column(name: 'nb_membres_max', type: Types::INTEGER)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le nombre max de membres doit être positif.')]
    #[Assert\LessThanOrEqual(100, message: 'Maximum 100 membres.')]
    private int $nbMembresMax = 10;

    #[ORM\Column(name: 'nb_membres_actuel', type: Types::INTEGER)]
    private int $nbMembresActuel = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $budget = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(name: 'couleur_equipe', type: Types::STRING, length: 7)]
    private ?string $couleurEquipe = '#3498db';

    #[ORM\Column(name: 'image_equipe', type: Types::STRING, length: 255, nullable: true)]
    private ?string $imageEquipe = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: MembreEquipe::class, cascade: ['persist', 'remove'])]
    private Collection $membres;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getIdEquipe(): ?int { return $this->idEquipe; }
    public function getNomEquipe(): ?string { return $this->nomEquipe; }
    public function setNomEquipe(?string $nomEquipe): static { $this->nomEquipe = $nomEquipe; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }
    public function getChefEquipeId(): ?int { return $this->chefEquipeId; }
    public function setChefEquipeId(?int $chefEquipeId): static { $this->chefEquipeId = $chefEquipeId; return $this; }
    public function getNbMembresMax(): int { return $this->nbMembresMax; }
    public function setNbMembresMax(int $nbMembresMax): static { $this->nbMembresMax = $nbMembresMax; return $this; }
    public function getNbMembresActuel(): int { return $this->nbMembresActuel; }
    public function setNbMembresActuel(int $nbMembresActuel): static { $this->nbMembresActuel = $nbMembresActuel; return $this; }
    public function getBudget(): ?string { return $this->budget; }
    public function setBudget(?string $budget): static { $this->budget = $budget; return $this; }
    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $departement): static { $this->departement = $departement; return $this; }
    public function getCouleurEquipe(): ?string { return $this->couleurEquipe; }
    public function setCouleurEquipe(?string $couleurEquipe): static { $this->couleurEquipe = $couleurEquipe; return $this; }
    public function getImageEquipe(): ?string { return $this->imageEquipe; }
    public function setImageEquipe(?string $imageEquipe): static { $this->imageEquipe = $imageEquipe; return $this; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function getMembres(): Collection { return $this->membres; }
    public function addMembre(MembreEquipe $membre): static
    {
        if (!$this->membres->contains($membre)) {
            $this->membres->add($membre);
            $membre->setEquipe($this);
        }
        return $this;
    }
    public function removeMembre(MembreEquipe $membre): static
    {
        if ($this->membres->removeElement($membre)) {
            if ($membre->getEquipe() === $this) {
                $membre->setEquipe(null);
            }
        }
        return $this;
    }
}
