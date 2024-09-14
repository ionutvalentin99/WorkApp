<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Company name',
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please insert company name',
                    ]),
                    new Length([
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('phone_number', TextType::class, [
                'attr' => [
                    'placeholder' => 'Phone number',
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please insert phone number',
                    ]),
                    new Length([
                        'max' => 20,
                    ]),
                ],
            ])
            ->add('country', TextType::class,[
                'attr' => [
                    'placeholder' => 'Country',
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please insert country',
                    ]),
                    new Length([
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'placeholder' => 'City',
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                    ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please insert city',
                    ]),
                    new Length([
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'Address',
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please insert company address',
                    ]),
                    new Length([
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('Submit', SubmitType::class, [
                'attr' => [
                    'class' => "block w-full shadow-sm border-transparent bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white rounded-md border p-2 mt-4 mb-2"
                ],
                'label' => 'Checkout'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
