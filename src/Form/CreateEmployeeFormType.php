<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<mixed>
 */
class CreateEmployeeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'       => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length(min: 2, max: 100),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label'       => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                    new Assert\Length(min: 2, max: 100),
                ],
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Email professionnel',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'email est obligatoire.']),
                    new Assert\Email(),
                ],
            ])
            ->add('telephone', TextType::class, [
                'label'    => 'Téléphone',
                'required' => false,
            ])
            ->add('adresse', TextType::class, [
                'label'    => 'Adresse',
                'required' => false,
            ])
            ->add('poste', TextType::class, [
                'label'       => 'Poste',
                'constraints' => [
                    new Assert\NotBlank(message: 'Le poste est obligatoire.'),
                ],
            ])
            ->add('salaire', MoneyType::class, [
                'label'       => 'Salaire',
                'currency'    => 'TND',  
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le salaire est obligatoire.']),
                    new Assert\Positive(message: 'Le salaire doit être positif.'),
                ],
            ])
            ->add('typeContrat', ChoiceType::class, [
                'label'   => 'Type de contrat',
                'choices' => [
                    'CDI'          => 'CDI',
                    'CDD'          => 'CDD',
                    'Stage'        => 'STAGE',
                    'Alternance'   => 'ALTERNANCE',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le type de contrat est obligatoire.'),
                ],
            ])
            ->add('departement', TextType::class, [
                'label'    => 'Département',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}