<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Wedding;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeddingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marie', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'label' => 'Marié',
                'attr' => ['class' => 'form-select']
            ])
            ->add('mariee', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'label' => 'Mariée',
                'attr' => ['class' => 'form-select']
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du mariage',
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
            ->add('musicians', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'label' => 'Musiciens',
                'multiple' => true,
                'expanded' => false, // true = cases à cocher, false = multi-select
                'attr' => ['class' => 'form-select', 'multiple' => 'multiple'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wedding::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'wedding_edit', // identifiant CSRF unique pour Wedding
        ]);
    }
}