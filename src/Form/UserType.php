<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
            ])

            ->add('firstname', TextType::class, [
                'label' => 'First Name:',
                'attr' => [
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'

                ],
            ])

            ->add('lastname', TextType::class, [
                'label' => 'Last Name:',
                'attr' => [
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2',
                    'label' => 'Last Name: '
                ],
            ])

            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
