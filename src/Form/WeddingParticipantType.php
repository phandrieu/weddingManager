<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeddingParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Adresse ligne 1',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('addressLine2', TextType::class, [
                'label' => 'Adresse ligne 2',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('addressPostalCodeAndCity', TextType::class, [
                'label' => 'Code postal & ville',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
