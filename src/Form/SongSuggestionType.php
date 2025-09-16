<?php

namespace App\Form;

use App\Entity\Song;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SongSuggestionType extends SongFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // Ici tu peux modifier les champs si besoin :
        // - rendre "type" optionnel ou masqué
        // - ajouter une logique différente
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'song_suggestion', // token spécifique
        ]);
    }
}