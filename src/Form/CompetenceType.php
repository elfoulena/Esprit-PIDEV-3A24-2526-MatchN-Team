<?php

namespace App\Form;

use App\Entity\Competence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<mixed>
 */
class CompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCompetence', TextType::class, [
                'attr' => ['placeholder' => 'Ex: JavaScript, Communication, Docker…', 'maxlength' => 255, 'autocomplete' => 'off'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de la compétence est obligatoire.']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le nom ne peut pas dépasser 255 caractères.']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => false,
                'expanded'    => true,
                'choices' => [
                    'Hard skill' => 'Hard skill',
                    'Soft skill' => 'Soft skill',
                    'Langue'     => 'Langue',
                    'Outil'      => 'Outil',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de compétence est obligatoire.']),
                ],
            ])
            ->add('descriptionCompetence', TextareaType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Décrivez brièvement cette compétence, son usage ou son niveau attendu…', 'rows' => 3],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
}
