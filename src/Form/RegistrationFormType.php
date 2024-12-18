<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $user = $event->getData();
            $userForm = $event->getForm();

            if ($user && $user->getFilename()) {
                $userForm->add('deleteCheckBox', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Cocher pour supprimer l\'image',
                ]);
            }

        });

        $builder
            ->add('pseudo',TextType::class,[
              'label' => 'Pseudo',
            ])
            ->add('email', EmailType::class, [
              'label'=>'Email'
            ])
          ->add('telephone',IntegerType::class,['label'=>'Téléphone'])
          ->add('prenom', TextType::class, [
            'label' => 'Prenom'
          ])
          ->add('nom', TextType::class, [
            'label' => 'Nom'
          ])

          ->add('campus', EntityType::class, [
            'class' => Campus::class,
            'choice_label' => 'nom',
            'placeholder' => 'Sélectionnez un campus',
            'label' => 'Campus',
            'required' => false,
          ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
              'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'Your image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '3000k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'The file must be an image'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
