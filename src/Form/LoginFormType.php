<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introdu email-ul',
                    'autocomplete' => 'email', // Ajută browserul să sugereze email-ul salvat
                ],
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'Te rugăm să introduci email-ul.']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Parolă',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introdu parola',
                    'autocomplete' => 'current-password', // Specific pentru login
                ],
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'Te rugăm să introduci parola.']),
                ],
            ])
            ->add('Submit', SubmitType::class, [
                'label' => 'Autentificare',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Păstrăm setările implicite similare cu register
            'attr' => [
                'autocomplete' => 'off'
            ]
        ]);
    }
}