<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
#[ORM\HasLifecycleCallbacks]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_evenement = null;

    #[ORM\OneToMany(targetEntity: ParticipationEvenement::class, mappedBy: 'evenement', orphanRemoval: true)]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    public function getId_evenement(): ?int
    {
        return $this->id_evenement;
    }

    public function setId_evenement(int $id_evenement): self
    {
        $this->id_evenement = $id_evenement;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 3, max: 100, minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le type d\'événement est obligatoire.')]
    private ?string $type_evenement = null;

    public function getType_evenement(): ?string
    {
        return $this->type_evenement;
    }

    public function setType_evenement(string $type_evenement): self
    {
        $this->type_evenement = $type_evenement;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    #[Assert\GreaterThanOrEqual('today', message: 'La date de début doit être aujourd\'hui ou dans le futur.')]
    private ?\DateTimeInterface $date_debut = null;

    public function getDate_debut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDate_debut(?\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    #[Assert\GreaterThan(propertyPath: 'date_debut', message: 'La date de fin doit être après la date de début.')]
    private ?\DateTimeInterface $date_fin = null;

    public function getDate_fin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDate_fin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    public function getDate_creation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: 'La deadline est obligatoire.')]
    #[Assert\LessThanOrEqual(propertyPath: 'date_debut', message: 'La deadline doit être avant ou égale à la date de début.')]
    private ?\DateTimeInterface $date_deadline = null;

    public function getDate_deadline(): ?\DateTimeInterface
    {
        return $this->date_deadline;
    }

    public function setDate_deadline(?\DateTimeInterface $date_deadline): self
    {
        $this->date_deadline = $date_deadline;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le lieu est obligatoire.')]
    #[Assert\Length(min: 2, max: 150, minMessage: 'Le lieu doit contenir au moins {{ limit }} caractères.')]
    private ?string $lieu = null;

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: 'La capacité maximale est obligatoire.')]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif.')]
    #[Assert\LessThanOrEqual(10000, message: 'La capacité ne peut pas dépasser 10 000.')]
    private ?int $capacite_max = null;

    public function getCapacite_max(): ?int
    {
        return $this->capacite_max;
    }

    public function setCapacite_max(int $capacite_max): self
    {
        $this->capacite_max = $capacite_max;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombre_actuel = null;

    public function getNombre_actuel(): ?int
    {
        return $this->nombre_actuel;
    }

    public function setNombre_actuel(?int $nombre_actuel): self
    {
        $this->nombre_actuel = $nombre_actuel;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    /**
     * @return Collection<int, ParticipationEvenement>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(ParticipationEvenement $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setEvenement($this);
        }

        return $this;
    }

    public function removeParticipation(ParticipationEvenement $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getEvenement() === $this) {
                $participation->setEvenement(null);
            }
        }

        return $this;
    }

    public function getIdEvenement(): ?int
    {
        return $this->id_evenement;
    }

    public function getTypeEvenement(): ?string
    {
        return $this->type_evenement;
    }

    public function setTypeEvenement(string $type_evenement): static
    {
        $this->type_evenement = $type_evenement;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTime $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateDeadline(): ?\DateTime
    {
        return $this->date_deadline;
    }

    public function setDateDeadline(\DateTime $date_deadline): static
    {
        $this->date_deadline = $date_deadline;

        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capacite_max;
    }

    public function setCapaciteMax(int $capacite_max): static
    {
        $this->capacite_max = $capacite_max;

        return $this;
    }

    public function getNombreActuel(): ?int
    {
        return $this->nombre_actuel;
    }

    public function setNombreActuel(?int $nombre_actuel): static
    {
        $this->nombre_actuel = $nombre_actuel;

        return $this;
    }

    /**
     * Calcule automatiquement le statut avant toute insertion ou mise à jour.
     * - 'prevu'    : date_debut est dans le futur
     * - 'en_cours' : on est entre date_debut et date_fin
     * - 'termine'  : date_fin est dans le passé
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function computeStatut(): void
    {
        $now = new \DateTime();

        if ($this->date_debut === null) {
            return;
        }

        if ($now < $this->date_debut) {
            $this->statut = 'prevu';
        } elseif ($this->date_fin !== null && $now <= $this->date_fin) {
            $this->statut = 'en_cours';
        } else {
            $this->statut = 'termine';
        }
    }

}
