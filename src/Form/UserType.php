<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', TextType::class)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Musicien' => 'ROLE_MUSICIAN',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('password', PasswordType::class, [
                'required' => $options['is_new'], // obligatoire si création
                'mapped' => false, // on gérera le hash dans le controller
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
                'help' => 'Laissez vide si vous ne voulez pas changer le mot de passe.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'user_edit',
            'is_new' => false, // option custom
        ]);
    }
}