<?php

namespace App\Form;

use App\Entity\CelebrationPeriod;
use App\Entity\SongType;
use App\Repository\CelebrationPeriodRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SongTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du type de chant',
                'attr' => ['class' => 'form-control']
            ])
            ->add('messe', ChoiceType::class, [
                'choices' => [
                    'Non' => 0,
                    'Oui' => 1,
                ],
                'expanded' => true,
            ])
            ->add('celebrationPeriod', EntityType::class, [
                'class' => CelebrationPeriod::class,
                'label' => 'Période de célébration',
                'required' => false,
                'placeholder' => 'Aucune période',
                'choice_label' => 'fullName',
                'query_builder' => function (CelebrationPeriodRepository $repo) {
                    return $repo->createQueryBuilder('cp')
                        ->orderBy('cp.periodOrder', 'ASC')
                        ->addOrderBy('cp.fullName', 'ASC');
                },
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SongType::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'song_type_edit', // <-- identifiant CSRF unique
        ]);
    }
}
