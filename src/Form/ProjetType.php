<?php

namespace App\Form;

use App\Entity\Competence;
use App\Entity\Projet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<Projet>
 */
class ProjetType extends AbstractType
{
    /**
     * @param FormBuilderInterface<Projet|null> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => ['placeholder' => 'Ex: Refonte du site e-commerce', 'maxlength' => 255],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre est obligatoire.']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le titre ne peut pas dépasser 255 caractères.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['placeholder' => 'Décrivez le projet, ses objectifs et son contexte…', 'rows' => 4],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Planifié'  => 'PLANIFIE',
                    'En cours'  => 'EN_COURS',
                    'En pause'  => 'EN_PAUSE',
                    'Terminé'   => 'TERMINE',
                    'Annulé'    => 'ANNULE',
                ],
                'placeholder' => false,
            ])
            ->add('priorite', ChoiceType::class, [
                'placeholder' => '— Sélectionner —',
                'choices' => [
                    'Haute'   => 'HAUTE',
                    'Moyenne' => 'MOYENNE',
                    'Basse'   => 'BASSE',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La priorité est obligatoire.']),
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire.']),
                ],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de fin est obligatoire.']),
                ],
            ])
            ->add('dateLivraison', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de livraison est obligatoire.']),
                ],
            ])
            ->add('budgetTotal', NumberType::class, [
                'scale' => 2,
                'attr' => ['placeholder' => '0.00', 'min' => '0', 'step' => '0.01'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le budget total est obligatoire.']),
                    new Assert\PositiveOrZero(['message' => 'Le budget total doit être positif ou nul.']),
                ],
            ])
            ->add('budgetInterne', NumberType::class, [
                'scale' => 2,
                'attr' => ['placeholder' => '0.00', 'min' => '0', 'step' => '0.01'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le budget interne est obligatoire.']),
                    new Assert\PositiveOrZero(['message' => 'Le budget interne doit être positif ou nul.']),
                ],
            ])
            ->add('budgetFreelance', NumberType::class, [
                'scale' => 2,
                'attr' => ['placeholder' => '0.00', 'min' => '0', 'step' => '0.01'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le budget freelance est obligatoire.']),
                    new Assert\PositiveOrZero(['message' => 'Le budget freelance doit être positif ou nul.']),
                ],
            ])
            ->add('visibleEmploye', CheckboxType::class, [
                'required' => false,
            ])
            ->add('visibleFreelancer', CheckboxType::class, [
                'required' => false,
            ])
            ->add('competences', EntityType::class, [
                'class'         => Competence::class,
                'choice_label'  => 'nomCompetence',
                'multiple'      => true,
                'expanded'      => true,
                'required'      => false,
                'by_reference'  => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
            'constraints' => [
                new Assert\Callback(function (Projet $projet, ExecutionContextInterface $context) {
                    $debut     = $projet->getDateDebut();
                    $fin       = $projet->getDateFin();
                    $livraison = $projet->getDateLivraison();

                    if ($debut && $fin && $fin < $debut) {
                        $context->buildViolation('La date de fin doit être après la date de début.')
                            ->atPath('dateFin')
                            ->addViolation();
                    }
                    if ($debut && $livraison && $livraison < $debut) {
                        $context->buildViolation('La date de livraison doit être après la date de début.')
                            ->atPath('dateLivraison')
                            ->addViolation();
                    }

                    $total     = (float) $projet->getBudgetTotal();
                    $interne   = (float) $projet->getBudgetInterne();
                    $freelance = (float) $projet->getBudgetFreelance();

                    if ($projet->getBudgetTotal() !== null && $projet->getBudgetInterne() !== null && $projet->getBudgetFreelance() !== null) {
                        if ($interne + $freelance > $total + 0.001) {
                            $context->buildViolation(
                                'La somme budget interne + freelance (' . number_format($interne + $freelance, 2) . ' TND) dépasse le budget total (' . number_format($total, 2) . ' TND).'
                            )
                                ->atPath('budgetInterne')
                                ->addViolation();
                        }
                    }
                }),
            ],
        ]);
    }
}
