<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UtilisateurRepository;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: ParticipationEvenement::class, mappedBy: 'utilisateur')]
    private Collection $participations;

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
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $github_username = null;

    public function getGithub_username(): ?string
    {
        return $this->github_username;
    }

    public function setGithub_username(?string $github_username): self
    {
        $this->github_username = $github_username;
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $prenom = null;

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $motDePasse = null;

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $poste = null;

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): self
    {
        $this->poste = $poste;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $salaire = null;

    public function getSalaire(): ?float
    {
        return $this->salaire;
    }

    public function setSalaire(?float $salaire): self
    {
        $this->salaire = $salaire;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $actif = null;

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $verified = null;

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(?bool $verified): self
    {
        $this->verified = $verified;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $verification_token = null;

    public function getVerification_token(): ?string
    {
        return $this->verification_token;
    }

    public function setVerification_token(?string $verification_token): self
    {
        $this->verification_token = $verification_token;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $telephone = null;

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $adresse = null;

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $typeContrat = null;

    public function getTypeContrat(): ?string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(?string $typeContrat): self
    {
        $this->typeContrat = $typeContrat;
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

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    public function getUpdated_at(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdated_at(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $verification_expiry = null;

    public function getVerification_expiry(): ?\DateTimeInterface
    {
        return $this->verification_expiry;
    }

    public function setVerification_expiry(\DateTimeInterface $verification_expiry): self
    {
        $this->verification_expiry = $verification_expiry;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $reset_token = null;

    public function getReset_token(): ?string
    {
        return $this->reset_token;
    }

    public function setReset_token(?string $reset_token): self
    {
        $this->reset_token = $reset_token;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reset_expiry = null;

    public function getReset_expiry(): ?\DateTimeInterface
    {
        return $this->reset_expiry;
    }

    public function setReset_expiry(?\DateTimeInterface $reset_expiry): self
    {
        $this->reset_expiry = $reset_expiry;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: AffectationProjet::class, mappedBy: 'utilisateur')]
    private Collection $affectationProjets;

    /**
     * @return Collection<int, AffectationProjet>
     */
    public function getAffectationProjets(): Collection
    {
        if (!$this->affectationProjets instanceof Collection) {
            $this->affectationProjets = new ArrayCollection();
        }
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

    #[ORM\OneToMany(targetEntity: CalendrierEquipe::class, mappedBy: 'utilisateur')]
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

    #[ORM\OneToMany(targetEntity: CommitHistory::class, mappedBy: 'utilisateur')]
    private Collection $commitHistorys;

    /**
     * @return Collection<int, CommitHistory>
     */
    public function getCommitHistorys(): Collection
    {
        if (!$this->commitHistorys instanceof Collection) {
            $this->commitHistorys = new ArrayCollection();
        }
        return $this->commitHistorys;
    }

    public function addCommitHistory(CommitHistory $commitHistory): self
    {
        if (!$this->getCommitHistorys()->contains($commitHistory)) {
            $this->getCommitHistorys()->add($commitHistory);
        }
        return $this;
    }

    public function removeCommitHistory(CommitHistory $commitHistory): self
    {
        $this->getCommitHistorys()->removeElement($commitHistory);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: DemandeEquipe::class, mappedBy: 'utilisateur')]
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

    #[ORM\OneToMany(targetEntity: DemandeEquipe::class, mappedBy: 'traitePar')]
    private Collection $demandesTraitees;

    /**
     * @return Collection<int, DemandeEquipe>
     */
    public function getDemandesTraitees(): Collection
    {
        if (!$this->demandesTraitees instanceof Collection) {
            $this->demandesTraitees = new ArrayCollection();
        }
        return $this->demandesTraitees;
    }

    public function addDemandesTraitee(DemandeEquipe $demandesTraitee): self
    {
        if (!$this->getDemandesTraitees()->contains($demandesTraitee)) {
            $this->getDemandesTraitees()->add($demandesTraitee);
        }
        return $this;
    }

    public function removeDemandesTraitee(DemandeEquipe $demandesTraitee): self
    {
        $this->getDemandesTraitees()->removeElement($demandesTraitee);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Equipe::class, mappedBy: 'utilisateur')]
    private Collection $equipes;

    /**
     * @return Collection<int, Equipe>
     */
    public function getEquipes(): Collection
    {
        if (!$this->equipes instanceof Collection) {
            $this->equipes = new ArrayCollection();
        }
        return $this->equipes;
    }

    public function addEquipe(Equipe $equipe): self
    {
        if (!$this->getEquipes()->contains($equipe)) {
            $this->getEquipes()->add($equipe);
        }
        return $this;
    }

    public function removeEquipe(Equipe $equipe): self
    {
        $this->getEquipes()->removeElement($equipe);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: RepoAccess::class, mappedBy: 'utilisateur')]
    private Collection $repoAccesss;

    /**
     * @return Collection<int, RepoAccess>
     */
    public function getRepoAccesss(): Collection
    {
        if (!$this->repoAccesss instanceof Collection) {
            $this->repoAccesss = new ArrayCollection();
        }
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

    #[ORM\ManyToMany(targetEntity: CompetenceF::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(
        name: 'freelancer_competence',
        joinColumns: [
            new ORM\JoinColumn(name: 'freelancer_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $competenceFs;

    public function __construct()
    {
        $this->affectationProjets = new ArrayCollection();
        $this->calendrierEquipes = new ArrayCollection();
        $this->commitHistorys = new ArrayCollection();
        $this->demandeEquipes = new ArrayCollection();
        $this->demandesTraitees = new ArrayCollection();
        $this->equipes = new ArrayCollection();
        $this->repoAccesss = new ArrayCollection();
        $this->competenceFs = new ArrayCollection();
        $this->participations = new ArrayCollection();
    }

    /**
     * @return Collection<int, CompetenceF>
     */
    public function getCompetenceFs(): Collection
    {
        if (!$this->competenceFs instanceof Collection) {
            $this->competenceFs = new ArrayCollection();
        }
        return $this->competenceFs;
    }

    public function addCompetenceF(CompetenceF $competenceF): self
    {
        if (!$this->getCompetenceFs()->contains($competenceF)) {
            $this->getCompetenceFs()->add($competenceF);
        }
        return $this;
    }

    public function removeCompetenceF(CompetenceF $competenceF): self
    {
        $this->getCompetenceFs()->removeElement($competenceF);
        return $this;
    }

    public function getGithubUsername(): ?string
    {
        return $this->github_username;
    }

    public function setGithubUsername(?string $github_username): static
    {
        $this->github_username = $github_username;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verification_token;
    }

    public function setVerificationToken(?string $verification_token): static
    {
        $this->verification_token = $verification_token;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getVerificationExpiry(): ?\DateTime
    {
        return $this->verification_expiry;
    }

    public function setVerificationExpiry(\DateTime $verification_expiry): static
    {
        $this->verification_expiry = $verification_expiry;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): static
    {
        $this->reset_token = $reset_token;
        return $this;
    }

    public function getResetExpiry(): ?\DateTime
    {
        return $this->reset_expiry;
    }

    public function setResetExpiry(?\DateTime $reset_expiry): static
    {
        $this->reset_expiry = $reset_expiry;
        return $this;
    }

    public function addRepoAccesss(RepoAccess $repoAccesss): static
    {
        if (!$this->repoAccesss->contains($repoAccesss)) {
            $this->repoAccesss->add($repoAccesss);
            $repoAccesss->setUtilisateur($this);
        }
        return $this;
    }

    public function removeRepoAccesss(RepoAccess $repoAccesss): static
    {
        if ($this->repoAccesss->removeElement($repoAccesss)) {
            if ($repoAccesss->getUtilisateur() === $this) {
                $repoAccesss->setUtilisateur(null);
            }
        }
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
            $participation->setUtilisateur($this);
        }
        return $this;
    }

    public function removeParticipation(ParticipationEvenement $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            if ($participation->getUtilisateur() === $this) {
                $participation->setUtilisateur(null);
            }
        }
        return $this;
    }
}