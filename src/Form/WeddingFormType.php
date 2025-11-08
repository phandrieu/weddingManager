<?php

namespace App\Form;

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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class WeddingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'label' => ' ',
                'required' => false,
            ])
            ->add('montantTotal', NumberType::class, [
                'label' => 'Montant total (€)',
                'required' => false,
                'scale' => 2,
                'empty_data' => '0',
                'attr' => ['class' => 'form-control', 'step' => '0.01']
            ])
            ->add('montantPaye', NumberType::class, [
                'label' => 'Montant déjà versé (€)',
                'required' => false,
                'scale' => 2,
                'empty_data' => '0',
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
            // Champs du marié
            ->add('marieFirstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieEmail', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieTelephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieAddressLine1', TextType::class, [
                'label' => 'Adresse ligne 1',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieAddressLine2', TextType::class, [
                'label' => 'Adresse ligne 2',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieAddressPostalCodeAndCity', TextType::class, [
                'label' => 'Code postal & ville',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            // Champs de la mariée
            ->add('marieeFirstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieeName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieeEmail', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieeTelephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieeAddressLine1', TextType::class, [
                'label' => 'Adresse ligne 1',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieeAddressLine2', TextType::class, [
                'label' => 'Adresse ligne 2',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('marieeAddressPostalCodeAndCity', TextType::class, [
                'label' => 'Code postal & ville',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $data = $event->getData();
            if (!$data instanceof Wedding) {
                return;
            }

            $form = $event->getForm();

            if ($data->getMarie()) {
                $form->add('marie', WeddingParticipantType::class, [
                    'label' => false,
                    'required' => false,
                ]);
            }

            if ($data->getMariee()) {
                $form->add('mariee', WeddingParticipantType::class, [
                    'label' => false,
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wedding::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'wedding_edit',
        ]);
    }
}