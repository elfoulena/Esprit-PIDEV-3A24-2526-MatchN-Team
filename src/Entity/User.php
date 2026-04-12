<?php

namespace App\Entity;

use App\Enum\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: "utilisateur")]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id")]
    private ?int $id = null;

    #[ORM\Column(name: "nom", length: 100)]
    private ?string $nom = null;

    #[ORM\Column(name: "prenom", length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(name: "email", length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(name: "motDePasse", length: 255)]
    private ?string $password = null;

    #[ORM\Column(name: "role", enumType: Role::class)]
    private ?Role $role = null;

    #[ORM\Column(name: "actif")]
    private bool $actif = true;

    #[ORM\Column(name: "verified")]
    private bool $verified = false;

    #[ORM\Column(name: "verification_token", nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(name: "telephone", length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(name: "adresse", length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: "totp_enabled", nullable: true)]
    private ?bool $totpEnabled = false;

    #[ORM\Column(name: "totp_secret", nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column(name: "verification_expiry", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $verificationExpiry = null;

    #[ORM\Column(name: "reset_token", nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(name: "reset_expiry", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $resetExpiry = null;

    #[ORM\Column(name: "github_username", length: 100, nullable: true)]
    private ?string $githubUsername = null;

    // EMPLOYE
    #[ORM\Column(name: "poste", length: 100, nullable: true)]
    private ?string $poste = null;

    #[ORM\Column(name: "salaire", nullable: true)]
    private ?float $salaire = null;

    #[ORM\Column(name: "typeContrat", length: 50, nullable: true)]
    private ?string $typeContrat = null;

    #[ORM\Column(name: "departement", length: 100, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // FREELANCER
    #[ORM\Column(name: "description_freelancer", type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: CompetenceF::class, inversedBy: 'freelancers')]
    #[ORM\JoinTable(
        name: 'freelancer_competence',     
        joinColumns: [
            new ORM\JoinColumn(name: 'freelancer_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $competences;

    #[ORM\OneToMany(targetEntity: ParticipationEvenement::class, mappedBy: 'utilisateur', orphanRemoval: true)]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->competences    = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    //GETTERS / SETTERS 

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = [];

        if ($this->role === Role::ADMIN_RH) {
            $roles[] = 'ROLE_ADMIN';
        }

        if ($this->role === Role::EMPLOYE) {
            $roles[] = 'ROLE_EMPLOYE';
        }

        if ($this->role === Role::FREELANCER) {
            $roles[] = 'ROLE_FREELANCER';
        }

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void {}

    public function getRole(): ?Role { return $this->role; }
    public function setRole(Role $role): static { $this->role = $role; return $this; }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }

    public function isVerified(): bool { return $this->verified; }
    public function setVerified(bool $verified): static { $this->verified = $verified; return $this; }

    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function setVerificationToken(?string $token): static { $this->verificationToken = $token; return $this; }

    public function getVerificationExpiry(): ?\DateTimeInterface { return $this->verificationExpiry; }
    public function setVerificationExpiry(?\DateTimeInterface $v): static { $this->verificationExpiry = $v; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }

    public function isTotpEnabled(): ?bool { return $this->totpEnabled; }
    public function setTotpEnabled(?bool $enabled): static { $this->totpEnabled = $enabled; return $this; }

    public function getTotpSecret(): ?string { return $this->totpSecret; }
    public function setTotpSecret(?string $secret): static { $this->totpSecret = $secret; return $this; }

    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $token): static { $this->resetToken = $token; return $this; }

    public function getResetExpiry(): ?\DateTimeInterface { return $this->resetExpiry; }
    public function setResetExpiry(?\DateTimeInterface $v): static { $this->resetExpiry = $v; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $v): static { $this->createdAt = $v; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $v): static { $this->updatedAt = $v; return $this; }

    // EMPLOYE
    public function getPoste(): ?string { return $this->poste; }
    public function setPoste(?string $poste): static { $this->poste = $poste; return $this; }

    public function getSalaire(): ?float { return $this->salaire; }
    public function setSalaire(?float $salaire): static { $this->salaire = $salaire; return $this; }

    public function getTypeContrat(): ?string { return $this->typeContrat; }
    public function setTypeContrat(?string $type): static { $this->typeContrat = $type; return $this; }

    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $departement): static { $this->departement = $departement; return $this; }

    // FREELANCER
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    // GITHUB
    public function getGithubUsername(): ?string { return $this->githubUsername; }
    public function setGithubUsername(?string $githubUsername): static { $this->githubUsername = $githubUsername; return $this; }

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
            // set the owning side to null (unless already changed)
            if ($participation->getUtilisateur() === $this) {
                $participation->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getCompetences(): Collection { return $this->competences; }

    public function addCompetence(CompetenceF $competence): static
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
        }
        return $this;
    }

    public function removeCompetence(CompetenceF $competence): static
    {
        $this->competences->removeElement($competence);
        return $this;
    }
}