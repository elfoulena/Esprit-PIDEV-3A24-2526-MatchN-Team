<?php

namespace App\Form;

use App\Entity\MembreEquipe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class MembreEquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom() . ' (#' . $user->getId() . ')';
                },
                'label' => 'Utilisateur',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('roleEquipe', ChoiceType::class, [
                'choices' => [
                    'Membre' => 'Membre',
                    'Chef d\'équipe' => 'Chef d\'équipe',
                    'Coordinateur' => 'Coordinateur',
                    'Observateur' => 'Observateur',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateAffectation', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('tauxParticipation', NumberType::class, [
                'attr' => ['class' => 'form-control', 'min' => 0, 'max' => 100, 'step' => 0.01],
            ])
            ->add('statutMembre', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'Actif',
                    'Inactif' => 'Inactif',
                    'En congé' => 'En congé',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('competencesPrincipales', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Java, SQL, Communication'],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Remarques éventuelles...', 'rows' => 4],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembreEquipe::class,
        ]);
    }
}