<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Wedding;
use App\Entity\Song;
use App\Entity\SongType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wedding::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'song_type_edit', // <-- identifiant CSRF unique
        ]);
    }
}