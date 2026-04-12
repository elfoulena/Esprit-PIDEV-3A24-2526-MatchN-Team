<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EvaluationPartTimeRepository;

#[ORM\Entity(repositoryClass: EvaluationPartTimeRepository::class)]
#[ORM\Table(name: 'evaluation_part_time')]
class EvaluationPartTime
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

    #[ORM\ManyToOne(targetEntity: AffectationProjet::class, inversedBy: 'evaluationPartTimes')]
    #[ORM\JoinColumn(name: 'affectation_id', referencedColumnName: 'id')]
    private ?AffectationProjet $affectationProjet = null;

    public function getAffectationProjet(): ?AffectationProjet
    {
        return $this->affectationProjet;
    }

    public function setAffectationProjet(?AffectationProjet $affectationProjet): self
    {
        $this->affectationProjet = $affectationProjet;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $note = null;

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_evaluation = null;

    public function getDate_evaluation(): ?\DateTimeInterface
    {
        return $this->date_evaluation;
    }

    public function setDate_evaluation(?\DateTimeInterface $date_evaluation): self
    {
        $this->date_evaluation = $date_evaluation;
        return $this;
    }

    public function getDateEvaluation(): ?\DateTime
    {
        return $this->date_evaluation;
    }

    public function setDateEvaluation(?\DateTime $date_evaluation): static
    {
        $this->date_evaluation = $date_evaluation;

        return $this;
    }

    public function getLabelNote(): string
    {
        $note = $this->note;
        if ($note === null) return 'Non noté';
        if ($note >= 9) return 'Excellent';
        if ($note >= 7) return 'Bien';
        if ($note >= 5) return 'Moyen';
        if ($note >= 3) return 'Insuffisant';
        return 'Très insuffisant';
    }

}
