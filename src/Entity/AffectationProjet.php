<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\AffectationProjetRepository;

#[ORM\Entity(repositoryClass: AffectationProjetRepository::class)]
#[ORM\Table(name: 'affectation_projet')]
class AffectationProjet
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'affectationProjets')]
    #[ORM\JoinColumn(name: 'User_id', referencedColumnName: 'id')]
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

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'affectationProjets')]
    #[ORM\JoinColumn(name: 'projet_id', referencedColumnName: 'id_projet')]
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

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_debut = null;

    public function getDate_debut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDate_debut(\DateTimeInterface $date_debut): self
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

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $taux_horaire = null;

    public function getTaux_horaire(): ?float
    {
        return $this->taux_horaire;
    }

    public function setTaux_horaire(?float $taux_horaire): self
    {
        $this->taux_horaire = $taux_horaire;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: EvaluationPartTime::class, mappedBy: 'affectationProjet')]
    private Collection $evaluationPartTimes;

    public function __construct()
    {
        $this->evaluationPartTimes = new ArrayCollection();
    }

    /**
     * @return Collection<int, EvaluationPartTime>
     */
    public function getEvaluationPartTimes(): Collection
    {
        if (!$this->evaluationPartTimes instanceof Collection) {
            $this->evaluationPartTimes = new ArrayCollection();
        }
        return $this->evaluationPartTimes;
    }

    public function addEvaluationPartTime(EvaluationPartTime $evaluationPartTime): self
    {
        if (!$this->getEvaluationPartTimes()->contains($evaluationPartTime)) {
            $this->getEvaluationPartTimes()->add($evaluationPartTime);
        }
        return $this;
    }

    public function removeEvaluationPartTime(EvaluationPartTime $evaluationPartTime): self
    {
        $this->getEvaluationPartTimes()->removeElement($evaluationPartTime);
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

    public function setDateFin(?\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getTauxHoraire(): ?float
    {
        return $this->taux_horaire;
    }

    public function setTauxHoraire(?float $taux_horaire): static
    {
        $this->taux_horaire = $taux_horaire;

        return $this;
    }

}
