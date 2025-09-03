<?php

namespace App\Form;

use App\Entity\Song;
use App\Entity\SongType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SongFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', EntityType::class, [
                'class' => SongType::class,
                'choice_label' => 'name',
                'label' => 'Type de chant',
                'attr' => ['class' => 'form-select']
            ])
            ->add('previewUrl', UrlType::class, [
                'label' => 'URL de prévisualisation',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('lyrics', TextareaType::class, [
                'label' => 'Paroles',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'song_type_edit', // <-- identifiant CSRF unique
        ]);
    }
}