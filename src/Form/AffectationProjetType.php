<?php

namespace App\Form;

use App\Entity\AffectationProjet;
use App\Entity\Projet;
use App\Entity\User;
use App\Enum\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

/**
 * @extends AbstractType<AffectationProjet>
 */
class AffectationProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('User', EntityType::class, [
                'class' => User::class,
                'label' => 'Freelancer *',
                'placeholder' => '-- Choisir un freelancer --',
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->andWhere('u.actif = true')
                        ->setParameter('role', Role::FREELANCER)
                        ->orderBy('u.nom', 'ASC');
                },
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'label' => 'Projet *',
                'placeholder' => '-- Choisir un projet --',
                'choice_label' => 'titre',
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début *',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'EN_ATTENTE',
                    'Acceptée' => 'ACCEPTEE',
                    'Refusée' => 'REFUSEE',
                    'Terminée' => 'TERMINEE',
                ],
            ])
            ->add('tauxHoraire', NumberType::class, [
                'label' => 'Taux horaire (DT/h)',
                'required' => false,
                'scale' => 2,
                'attr' => ['placeholder' => 'Ex: 25.00', 'min' => '0', 'max' => '1000', 'step' => '0.01'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AffectationProjet::class,
        ]);
    }
}
