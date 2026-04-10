<?php

namespace App\Form;

use App\Entity\AffectationProjet;
use App\Entity\EvaluationPartTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EvaluationPartTimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('affectationProjet', EntityType::class, [
                'class' => AffectationProjet::class,
                'label' => 'Affectation *',
                'placeholder' => '-- Choisir une affectation --',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.User', 'u')
                        ->addSelect('u')
                        ->leftJoin('a.projet', 'p')
                        ->addSelect('p')
                        ->where('a.statut IN (:statuts)')
                        ->setParameter('statuts', ['ACCEPTEE', 'TERMINEE'])
                        ->orderBy('a.date_debut', 'DESC');
                },
            ])
            ->add('note', IntegerType::class, [
                'label' => 'Note (0-10) *',
                'attr' => ['type' => 'range', 'min' => '0', 'max' => '10', 'step' => '1'],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => ['placeholder' => 'Votre commentaire...', 'rows' => 4, 'maxlength' => 1000],
            ])
            ->add('dateEvaluation', DateType::class, [
                'label' => 'Date d\'évaluation',
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvaluationPartTime::class,
        ]);
    }
}
