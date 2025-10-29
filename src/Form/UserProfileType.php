<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserProfileType extends AbstractType
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
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Adresse ligne 1',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('addressLine2', TextType::class, [
                'label' => 'Adresse ligne 2',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('addressPostalCodeAndCity', TextType::class, [
                'label' => 'Code postal & Ville',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
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
            'csrf_token_id'   => 'user_profile',
        ]);
    }
}
