<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Utilizator',
                'constraints' => [
                    new NotBlank(['message' => 'Please insert username']),
                    new Length(['min' => 3, 'max' => 180]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introdu numele de utilizator',
                    'autocomplete' => 'off',
                ],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prenume',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introdu prenumele',
                    'autocomplete' => 'off',
                ],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nume',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introdu numele',
                    'autocomplete' => 'off',
                ],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introdu email-ul',
                    // poți folosi 'email' pentru a permite completarea corectă, dar dacă vrei să o oprești:
                    'autocomplete' => 'off',
                ],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Parolele nu coincid.',
                'first_options' => [
                    'label' => 'Parolă',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Introdu parola',
                        // pentru parole noi folosește new-password
                        'autocomplete' => 'new-password',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                ],
                'second_options' => [
                    'label' => 'Repetă parola',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Repetă parola',
                        'autocomplete' => 'new-password',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please insert a password']),
                    new Length(['min' => 6, 'minMessage' => 'Password too short'])
                ],
            ])
            ->add('Submit', SubmitType::class, [
                'label' => 'Înregistrare',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg w-100'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => true,
            'attr' => [
                'autocomplete' => 'off'
            ]
        ]);
    }
}
