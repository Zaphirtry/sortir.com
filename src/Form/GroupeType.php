<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class GroupeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du groupe',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('membre', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'pseudo',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'form-check'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,
        ]);
    }
}
