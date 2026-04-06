<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\ParticipationEvenement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('type_evenement', ChoiceType::class, [
                'choices' => [
                    'Compétition' => 'COMPETITION',
                    'Meetup' => 'MEETUP',
                    'Porte Ouverte' => 'PORTE_OUVERTE',
                    'Conférence' => 'CONFERENCE',
                    'Workshop' => 'WORKSHOP',
                    'Formation' => 'FORMATION',
                ],
            ])
            ->add('date_debut', null, [
                'widget' => 'single_text',
            ])
            ->add('date_fin', null, [
                'widget' => 'single_text',
            ])
            ->add('date_deadline', null, [
                'widget' => 'single_text',
            ])
            ->add('lieu')
            ->add('capacite_max')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
