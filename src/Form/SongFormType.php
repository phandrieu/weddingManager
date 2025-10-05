<?php

namespace App\Form;

use App\Entity\Song;
use App\Entity\SongType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SongFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champs communs
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', EntityType::class, [
                'class' => SongType::class,
                'choice_label' => 'name',
                'label' => 'Type d\'élément',
                'attr' => ['class' => 'form-select']
            ])
            // choix Chant / Texte : doit rester présent pour décider quels champs afficher
            ->add('song', ChoiceType::class, [
                'choices' => [
                    'Lecture et Prière' => 0,
                    'Chant' => 1,
                ],
                'expanded' => true,
                'label' => 'Type d\'élément',
            ])
            // champs de base affichés pour les deux cas
            ->add('lyrics', TextareaType::class, [
                'label' => 'Texte',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ]);

        // fonction utilitaire pour ajouter les champs spécifiques Chant
        $addSongFields = function ($form) {
            $form
                ->add('previewUrl', UrlType::class, [
                    'label' => 'URL de prévisualisation',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
                // auteur des paroles pour les chants
                ->add('lyricsAuthorName', TextType::class, [
                    'label' => 'Auteur des paroles',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('partitionPDFFile', FileType::class, [
                    'label' => 'Partition PDF',
                    'required' => false,
                    'mapped' => true,
                    'attr' => ['accept' => 'application/pdf', 'class' => 'form-control'],
                ])
                ->add('musicAuthorName', TextType::class, [
                    'label' => 'Auteur musique',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('interpretName', TextType::class, [
                    'label' => 'Interprète',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('editorName', TextType::class, [
                    'label' => 'Éditeur',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ]);
        };

        // fonction utilitaire pour ajouter les champs spécifiques Texte
        $addTextFields = function ($form) {
            $form
                ->add('textRef', TextType::class, [
                    'label' => 'Référence du texte',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('textTranslationName', TextType::class, [
                    'label' => 'Traduction / référence de traduction',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ]);
        };

        // Ajout des deux groupes de champs au rendu initial pour que le JS puisse les afficher/cacher
        $addSongFields($builder);
        $addTextFields($builder);

        // garder un listener pour assurer la présence du champ suggestion lors du submit si besoin
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            if (!$form->has('suggestion')) {
                $form->add('suggestion', CheckboxType::class, [
                    'label' => 'Suggestion',
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'song_type_edit',
        ]);
    }
}
