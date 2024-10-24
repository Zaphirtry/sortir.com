<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class FiltresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
      $builder
        ->add('nom', TextType::class, [
          'label' => 'Nom de la sortie',
          'required' => false,
        ])
        ->add('dateDebut', DateType::class, [
          'label' => 'Date de début',
          'widget' => 'single_text',
          'required' => false,
        ])
        ->add('dateFin', DateType::class, [
          'label' => 'Date de fin',
          'widget' => 'single_text',
          'required' => false,
        ])
      ->add('campus', EntityType::class, [
       'class' => Campus::class,
      'choice_label' => 'nom',
      'placeholder' => 'Sélectionnez un campus',
      'label' => 'Campus',
      'required' => false,
    ])
      ->add('mesSortiesOrganisees', CheckboxType::class, [
        'label' => 'Mes sorties organisées',
        'required' => false,
      ])
      ->add('mesSortiesParticipe', CheckboxType::class, [
        'label' => 'Les sorties auxquelles je participe',
        'required' => false,
      ])
      ->add('sortiesPassees', CheckboxType::class, [
        'label' => 'Les sorties passées',
        'required' => false,
      ])
        ->add('sortiesAnnulee', CheckboxType::class, [
          'label' => 'Les sorties annulées',
          'required' => false,
        ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
