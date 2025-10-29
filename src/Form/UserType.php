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
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profilePictureFile', VichImageType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer la photo',
                'download_label' => 'Télécharger',
                'download_uri' => false,
                'image_uri' => true,
                'imagine_pattern' => 'profile_thumb',
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])
            ->add('firstName', TextType::class, options: ['label' => 'Prénom', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('name', TextType::class, options: ['label' => 'Nom', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('email', EmailType::class, options: ['label' => 'Email', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('telephone', TextType::class, options: ['label' => 'Téléphone', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('addressLine1', TextType::class, options: ['label' => 'Adresse ligne 1', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('addressLine2', TextType::class, options: ['label' => 'Adresse ligne 2', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('addressPostalCodeAndCity', TextType::class, options: ['label' => 'Code postal & Ville', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Musicien' => 'ROLE_MUSICIAN',
                    'Paroisse' => 'ROLE_PARISH',
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