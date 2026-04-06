<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EquipeRepository;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
#[ORM\Table(name: 'equipe')]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_equipe = null;

    public function getId_equipe(): ?int
    {
        return $this->id_equipe;
    }

    public function setId_equipe(int $id_equipe): self
    {
        $this->id_equipe = $id_equipe;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_equipe = null;

    public function getNom_equipe(): ?string
    {
        return $this->nom_equipe;
    }

    public function setNom_equipe(string $nom_equipe): self
    {
        $this->nom_equipe = $nom_equipe;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
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

    #[ORM\Column(type: 'date', nullable: false)]
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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'equipes')]
    #[ORM\JoinColumn(name: 'chef_equipe_id', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $nb_membres_max = null;

    public function getNb_membres_max(): ?int
    {
        return $this->nb_membres_max;
    }

    public function setNb_membres_max(int $nb_membres_max): self
    {
        $this->nb_membres_max = $nb_membres_max;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $nb_membres_actuel = null;

    public function getNb_membres_actuel(): ?int
    {
        return $this->nb_membres_actuel;
    }

    public function setNb_membres_actuel(int $nb_membres_actuel): self
    {
        $this->nb_membres_actuel = $nb_membres_actuel;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $budget = null;

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $departement = null;

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): self
    {
        $this->departement = $departement;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $couleur_equipe = null;

    public function getCouleur_equipe(): ?string
    {
        return $this->couleur_equipe;
    }

    public function setCouleur_equipe(?string $couleur_equipe): self
    {
        $this->couleur_equipe = $couleur_equipe;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image_equipe = null;

    public function getImage_equipe(): ?string
    {
        return $this->image_equipe;
    }

    public function setImage_equipe(?string $image_equipe): self
    {
        $this->image_equipe = $image_equipe;
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

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $updated_at = null;

    public function getUpdated_at(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdated_at(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: CalendrierEquipe::class, mappedBy: 'equipe')]
    private Collection $calendrierEquipes;

    /**
     * @return Collection<int, CalendrierEquipe>
     */
    public function getCalendrierEquipes(): Collection
    {
        if (!$this->calendrierEquipes instanceof Collection) {
            $this->calendrierEquipes = new ArrayCollection();
        }
        return $this->calendrierEquipes;
    }

    public function addCalendrierEquipe(CalendrierEquipe $calendrierEquipe): self
    {
        if (!$this->getCalendrierEquipes()->contains($calendrierEquipe)) {
            $this->getCalendrierEquipes()->add($calendrierEquipe);
        }
        return $this;
    }

    public function removeCalendrierEquipe(CalendrierEquipe $calendrierEquipe): self
    {
        $this->getCalendrierEquipes()->removeElement($calendrierEquipe);
        return $this;
    }

    #[ORM\OneToOne(targetEntity: CompetenceEquipe::class, mappedBy: 'equipe')]
    private ?CompetenceEquipe $competenceEquipe = null;

    public function getCompetenceEquipe(): ?CompetenceEquipe
    {
        return $this->competenceEquipe;
    }

    public function setCompetenceEquipe(?CompetenceEquipe $competenceEquipe): self
    {
        $this->competenceEquipe = $competenceEquipe;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: DemandeEquipe::class, mappedBy: 'equipe')]
    private Collection $demandeEquipes;

    /**
     * @return Collection<int, DemandeEquipe>
     */
    public function getDemandeEquipes(): Collection
    {
        if (!$this->demandeEquipes instanceof Collection) {
            $this->demandeEquipes = new ArrayCollection();
        }
        return $this->demandeEquipes;
    }

    public function addDemandeEquipe(DemandeEquipe $demandeEquipe): self
    {
        if (!$this->getDemandeEquipes()->contains($demandeEquipe)) {
            $this->getDemandeEquipes()->add($demandeEquipe);
        }
        return $this;
    }

    public function removeDemandeEquipe(DemandeEquipe $demandeEquipe): self
    {
        $this->getDemandeEquipes()->removeElement($demandeEquipe);
        return $this;
    }

    #[ORM\OneToOne(targetEntity: MembreEquipe::class, mappedBy: 'equipe')]
    private ?MembreEquipe $membreEquipe = null;

    public function getMembreEquipe(): ?MembreEquipe
    {
        return $this->membreEquipe;
    }

    public function setMembreEquipe(?MembreEquipe $membreEquipe): self
    {
        $this->membreEquipe = $membreEquipe;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'equipe')]
    private Collection $messages;

    public function __construct()
    {
        $this->calendrierEquipes = new ArrayCollection();
        $this->demandeEquipes = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        if (!$this->messages instanceof Collection) {
            $this->messages = new ArrayCollection();
        }
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->getMessages()->contains($message)) {
            $this->getMessages()->add($message);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        $this->getMessages()->removeElement($message);
        return $this;
    }

    public function getIdEquipe(): ?int
    {
        return $this->id_equipe;
    }

    public function getNomEquipe(): ?string
    {
        return $this->nom_equipe;
    }

    public function setNomEquipe(string $nom_equipe): static
    {
        $this->nom_equipe = $nom_equipe;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getNbMembresMax(): ?int
    {
        return $this->nb_membres_max;
    }

    public function setNbMembresMax(int $nb_membres_max): static
    {
        $this->nb_membres_max = $nb_membres_max;

        return $this;
    }

    public function getNbMembresActuel(): ?int
    {
        return $this->nb_membres_actuel;
    }

    public function setNbMembresActuel(int $nb_membres_actuel): static
    {
        $this->nb_membres_actuel = $nb_membres_actuel;

        return $this;
    }

    public function getCouleurEquipe(): ?string
    {
        return $this->couleur_equipe;
    }

    public function setCouleurEquipe(?string $couleur_equipe): static
    {
        $this->couleur_equipe = $couleur_equipe;

        return $this;
    }

    public function getImageEquipe(): ?string
    {
        return $this->image_equipe;
    }

    public function setImageEquipe(?string $image_equipe): static
    {
        $this->image_equipe = $image_equipe;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

}
