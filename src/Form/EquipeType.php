<?php

namespace App\Form;

use App\Entity\Equipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEquipe', TextType::class, [
                'label' => 'Nom de l\'équipe *',
                'attr'  => ['placeholder' => 'Ex: Équipe Innovation'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['placeholder' => 'Décrivez l\'équipe...', 'rows' => 4],
            ])
            ->add('dateCreation', DateType::class, [
                'label'  => 'Date de création *',
                'widget' => 'single_text',
            ])
            ->add('statut', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => [
                    'Active'   => 'Active',
                    'Inactive' => 'Inactive',
                    'En pause' => 'En pause',
                ],
            ])
            ->add('departement', ChoiceType::class, [
                'label'    => 'Département',
                'required' => false,
                'placeholder' => '-- Choisir --',
                'choices'  => [
                    'Développement'  => 'Développement',
                    'Marketing'      => 'Marketing',
                    'Ventes'         => 'Ventes',
                    'Support'        => 'Support',
                    'RH'             => 'RH',
                    'Finance'        => 'Finance',
                    'Direction'      => 'Direction',
                    'Autre'          => 'Autre',
                ],
            ])
            ->add('nbMembresMax', IntegerType::class, [
                'label' => 'Nombre max de membres',
                'attr'  => ['min' => 1, 'max' => 100],
            ])
            ->add('budget', NumberType::class, [
                'label'    => 'Budget (DT)',
                'required' => false,
                'scale'    => 2,
                'attr'     => ['placeholder' => 'Ex: 50000'],
            ])
            ->add('couleurEquipe', ColorType::class, [
                'label' => 'Couleur de l\'équipe',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}
