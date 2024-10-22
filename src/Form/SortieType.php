<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => [
                    'placeholder' => 'ex : Cinéma',
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'min' => 30,
                    'max' => 1440, // 24 heures
                ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre maximum d\'inscriptions',
                'attr' => [
                    'min' => 2,
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
            ])
            ->add('etat', TextType::class, [
                'label' => 'État',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}