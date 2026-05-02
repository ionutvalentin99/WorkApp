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
                'label' => 'Nume companie',
                'attr' => [
                    'placeholder' => 'ex: Acme SRL',
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Introduceți numele companiei.']),
                    new Length(['max' => 100]),
                ],
            ])
            ->add('phone_number', TextType::class, [
                'label' => 'Telefon',
                'attr' => [
                    'placeholder' => 'ex: 0721 000 000',
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Introduceți numărul de telefon.']),
                    new Length(['max' => 20]),
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Țară',
                'attr' => [
                    'placeholder' => 'ex: România',
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Introduceți țara.']),
                    new Length(['max' => 50]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Oraș',
                'attr' => [
                    'placeholder' => 'ex: București',
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Introduceți orașul.']),
                    new Length(['max' => 50]),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresă',
                'attr' => [
                    'placeholder' => 'ex: Str. Exemplu, nr. 1',
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Introduceți adresa.']),
                    new Length(['max' => 100]),
                ],
            ])
            ->add('Submit', SubmitType::class, [
                'label' => 'Continuă spre plată',
                'attr' => ['class' => 'btn btn-primary w-100'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
