<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Wedding;
use App\Entity\Song;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            ->add('time', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('parish', TextType::class, [
                'label' => 'Paroisse',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('church', TextType::class, [
                'label' => 'Église',
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
            /*->add('musicians', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'label' => 'Musiciens',
                'multiple' => true,
                'expanded' => false,
                'attr' => ['class' => 'form-select', 'multiple' => 'multiple'],
            ])*/
            ->add('songs', EntityType::class, [
                'class' => Song::class,
                'choice_label' => 'name',
                'label' => 'Chants (répertoire)',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-select', 'multiple' => 'multiple'],
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Archivé',
                'required' => false,
            ])
            ->add('messe', CheckboxType::class, [
                'label' => 'Messe (coché = messe, décoché = célébration)',
                'required' => false,
            ])
            ->add('montantTotal', NumberType::class, [
                'label' => 'Montant total (€)',
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'step' => '0.01']
            ])
            ->add('montantPaye', NumberType::class, [
                'label' => 'Montant déjà versé (€)',
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'step' => '0.01']
            ])
            ->add('priestFirstName', TextType::class, [
                'label' => 'Prêtre - prénom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('priestLastName', TextType::class, [
                'label' => 'Prêtre - nom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('priestPhoneNumber', TextType::class, [
                'label' => 'Prêtre - téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('priestEMail', EmailType::class, [
                'label' => 'Prêtre - email',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wedding::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'wedding_edit',
        ]);
    }
}