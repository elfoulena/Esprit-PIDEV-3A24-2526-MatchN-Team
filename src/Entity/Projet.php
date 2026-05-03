<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProjetRepository;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
#[ORM\Table(name: 'projet')]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_projet = null;

    public function getId_projet(): ?int
    {
        return $this->id_projet;
    }

    public function setId_projet(int $id_projet): self
    {
        $this->id_projet = $id_projet;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private string $titre = '';

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
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

    #[ORM\Column(type: 'date', nullable: true)]
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

    #[ORM\Column(type: 'date', nullable: true)]
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

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_livraison = null;

    public function getDate_livraison(): ?\DateTimeInterface
    {
        return $this->date_livraison;
    }

    public function setDate_livraison(?\DateTimeInterface $date_livraison): self
    {
        $this->date_livraison = $date_livraison;
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

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $budget_total = null;
    public function getBudget_total(): ?string
    {
        return $this->budget_total;
    }

    public function setBudget_total(?string $budget_total): self
    {
        $this->budget_total = $budget_total;
        return $this;
    }

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $budget_interne = null;

    public function getBudget_interne(): ?string
    {
        return $this->budget_interne;
    }

    public function setBudget_interne(?string $budget_interne): self
    {
        $this->budget_interne = $budget_interne;
        return $this;
    }

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $budget_freelance = null;

    public function getBudget_freelance(): ?string
    {
        return $this->budget_freelance;
    }

    public function setBudget_freelance(?string $budget_freelance): self
    {
        $this->budget_freelance = $budget_freelance;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $priorite = null;

    public function getPriorite(): ?string { return $this->priorite; }
    public function setPriorite(?string $priorite): self { $this->priorite = $priorite; return $this; }

    #[ORM\Column(name: 'visible_employe', type: 'boolean', options: ['default' => false])]
    private bool $visibleEmploye = false;

    public function isVisibleEmploye(): bool { return $this->visibleEmploye; }
    public function setVisibleEmploye(bool $v): self { $this->visibleEmploye = $v; return $this; }

    #[ORM\Column(name: 'visible_freelancer', type: 'boolean', options: ['default' => false])]
    private bool $visibleFreelancer = false;

    public function isVisibleFreelancer(): bool { return $this->visibleFreelancer; }
    public function setVisibleFreelancer(bool $v): self { $this->visibleFreelancer = $v; return $this; }

    /** @var Collection<int, AffectationProjet> */
    #[ORM\OneToMany(targetEntity: AffectationProjet::class, mappedBy: 'projet')]
    private Collection $affectationProjets;

    /**
     * @return Collection<int, AffectationProjet>
     */
    public function getAffectationProjets(): Collection
    {
        return $this->affectationProjets;
    }

    public function addAffectationProjet(AffectationProjet $affectationProjet): self
    {
        if (!$this->getAffectationProjets()->contains($affectationProjet)) {
            $this->getAffectationProjets()->add($affectationProjet);
        }
        return $this;
    }

    public function removeAffectationProjet(AffectationProjet $affectationProjet): self
    {
        $this->getAffectationProjets()->removeElement($affectationProjet);
        return $this;
    }

    /** @var Collection<int, DemandeParticipation> */
    #[ORM\OneToMany(
        targetEntity: DemandeParticipation::class,
        mappedBy: 'projet',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $demandeParticipations;

    /**
     * @return Collection<int, DemandeParticipation>
     */
    public function getDemandeParticipations(): Collection
    {
        return $this->demandeParticipations;
    }

    public function addDemandeParticipation(DemandeParticipation $demandeParticipation): self
    {
        if (!$this->getDemandeParticipations()->contains($demandeParticipation)) {
            $this->getDemandeParticipations()->add($demandeParticipation);
            $demandeParticipation->setProjet($this);
        }
        return $this;
    }

    public function removeDemandeParticipation(DemandeParticipation $demandeParticipation): self
    {
        if ($this->getDemandeParticipations()->removeElement($demandeParticipation)) {
            if ($demandeParticipation->getProjet() === $this) {
                $demandeParticipation->setProjet(null);
            }
        }
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Repository::class, mappedBy: 'projet')]
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

    /** @var Collection<int, Competence> */
    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'projets')]
    #[ORM\JoinTable(name: 'projet_competence',
        joinColumns: [new ORM\JoinColumn(name: 'projet_id', referencedColumnName: 'id_projet')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id_competence')]
    )]
    private Collection $competences;

    public function __construct()
    {
        $this->affectationProjets = new ArrayCollection();
        $this->demandeParticipations = new ArrayCollection();
        $this->competences = new ArrayCollection();
    }

    /**
     * @return Collection<int, Competence>
     */
    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function addCompetence(Competence $competence): self
    {
        if (!$this->getCompetences()->contains($competence)) {
            $this->getCompetences()->add($competence);
        }
        return $this;
    }

    public function removeCompetence(Competence $competence): self
    {
        $this->getCompetences()->removeElement($competence);
        return $this;
    }

    public function getIdProjet(): ?int
    {
        return $this->id_projet;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->date_livraison;
    }

    public function setDateLivraison(?\DateTimeInterface $date_livraison): static
    {
        $this->date_livraison = $date_livraison;

        return $this;
    }

    public function getBudgetTotal(): ?string
    {
        return $this->budget_total;
    }

    public function setBudgetTotal(?string $budget_total): static
    {
        $this->budget_total = $budget_total;

        return $this;
    }

    public function getBudgetInterne(): ?string
    {
        return $this->budget_interne;
    }

    public function setBudgetInterne(?string $budget_interne): static
    {
        $this->budget_interne = $budget_interne;

        return $this;
    }

    public function getBudgetFreelance(): ?string
    {
        return $this->budget_freelance;
    }

    public function setBudgetFreelance(?string $budget_freelance): static
    {
        $this->budget_freelance = $budget_freelance;

        return $this;
    }

}
