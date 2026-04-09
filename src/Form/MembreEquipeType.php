<?php

namespace App\Form;

use App\Entity\MembreEquipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembreEquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idUser', IntegerType::class, [
                'label' => 'ID Utilisateur *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'ID de l\'employé',
                    'min' => 1
                ]
            ])
            ->add('roleEquipe', ChoiceType::class, [
                'label'   => 'Rôle dans l\'équipe',
                'choices' => [
                    'Membre' => 'Membre',
                    'Chef d\'équipe' => 'Chef d\'équipe',
                    'Coordinateur' => 'Coordinateur',
                    'Observateur' => 'Observateur',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateAffectation', DateType::class, [
                'label'  => 'Date d\'affectation *',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateFin', DateType::class, [
                'label'    => 'Date de fin',
                'required' => false,
                'widget'   => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('tauxParticipation', NumberType::class, [
                'label' => 'Taux de participation (%)',
                'scale' => 2,
                'attr'  => ['min' => 0, 'max' => 100, 'step' => '0.01', 'class' => 'form-control'],
            ])
            ->add('statutMembre', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => [
                    'Actif' => 'Actif',
                    'Inactif' => 'Inactif',
                    'En congé' => 'En congé',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('competencesPrincipales', TextType::class, [
                'label'    => 'Compétences principales',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex: Java, SQL, Communication', 'class' => 'form-control'],
            ])
            ->add('notes', TextareaType::class, [
                'label'    => 'Notes',
                'required' => false,
                'attr'     => ['placeholder' => 'Remarques...', 'rows' => 3, 'class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembreEquipe::class,
        ]);
    }
}