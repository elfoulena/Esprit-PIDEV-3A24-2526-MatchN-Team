<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class FreelancerProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',    TextType::class,  ['label' => 'Nom'])
            ->add('prenom', TextType::class,  ['label' => 'Prénom'])
            ->add('email',  EmailType::class, ['label' => 'Email'])
            ->add('telephone', TextType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('adresse',   TextType::class, ['label' => 'Adresse',   'required' => false])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,
                'required'       => false,
                'first_options'  => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'constraints'    => [new Length(['min' => 8, 'minMessage' => 'Minimum 8 caractères.'])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}