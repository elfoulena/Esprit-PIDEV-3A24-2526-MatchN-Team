<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CalendrierEquipeRepository;

#[ORM\Entity(repositoryClass: CalendrierEquipeRepository::class)]
#[ORM\Table(name: 'calendrier_equipe')]
class CalendrierEquipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_calendrier = null;

    public function getId_calendrier(): ?int
    {
        return $this->id_calendrier;
    }

    public function setId_calendrier(int $id_calendrier): self
    {
        $this->id_calendrier = $id_calendrier;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'calendrierEquipes')]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id_equipe')]
    private ?Equipe $equipe = null;

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre_evenement = null;

    public function getTitre_evenement(): ?string
    {
        return $this->titre_evenement;
    }

    public function setTitre_evenement(string $titre_evenement): self
    {
        $this->titre_evenement = $titre_evenement;
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

    #[ORM\Column(type: 'datetime', nullable: false)]
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

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_fin = null;

    public function getDate_fin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDate_fin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $type_evenement = null;

    public function getType_evenement(): ?int
    {
        return $this->type_evenement;
    }

    public function setType_evenement(?int $type_evenement): self
    {
        $this->type_evenement = $type_evenement;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $lieu = null;

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $lien_visio = null;

    public function getLien_visio(): ?string
    {
        return $this->lien_visio;
    }

    public function setLien_visio(?string $lien_visio): self
    {
        $this->lien_visio = $lien_visio;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $priorite = null;

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): self
    {
        $this->priorite = $priorite;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $rappel = null;

    public function isRappel(): ?bool
    {
        return $this->rappel;
    }

    public function setRappel(?bool $rappel): self
    {
        $this->rappel = $rappel;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rappel_minutes = null;

    public function getRappel_minutes(): ?int
    {
        return $this->rappel_minutes;
    }

    public function setRappel_minutes(?int $rappel_minutes): self
    {
        $this->rappel_minutes = $rappel_minutes;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $couleur = null;

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'calendrierEquipes')]
    #[ORM\JoinColumn(name: 'createur_id', referencedColumnName: 'id')]
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

    public function getIdCalendrier(): ?int
    {
        return $this->id_calendrier;
    }

    public function getTitreEvenement(): ?string
    {
        return $this->titre_evenement;
    }

    public function setTitreEvenement(string $titre_evenement): static
    {
        $this->titre_evenement = $titre_evenement;

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

    public function getTypeEvenement(): ?int
    {
        return $this->type_evenement;
    }

    public function setTypeEvenement(?int $type_evenement): static
    {
        $this->type_evenement = $type_evenement;

        return $this;
    }

    public function getLienVisio(): ?string
    {
        return $this->lien_visio;
    }

    public function setLienVisio(?string $lien_visio): static
    {
        $this->lien_visio = $lien_visio;

        return $this;
    }

    public function getRappelMinutes(): ?int
    {
        return $this->rappel_minutes;
    }

    public function setRappelMinutes(?int $rappel_minutes): static
    {
        $this->rappel_minutes = $rappel_minutes;

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
