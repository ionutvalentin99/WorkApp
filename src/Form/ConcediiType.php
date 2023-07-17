<?php

namespace App\Form;

use App\Entity\Concedii;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcediiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'mapped' => true,
                'widget' => 'single_text',
                'data' => new DateTime(),
                'label' => 'De la: ',
                'attr' => [
                    'class' => 'block dark:text-black rounded-full',
                    'name' => 'startDate'
                ]
            ])

            ->add('endDate', DateType::class, [
                'mapped' => true,
                'widget' => 'single_text',
                'data' => new DateTime(),
                'label' => 'pana la: ',
                'attr' => [
                    'class' => 'block dark:text-black rounded-full',
                    'name' => 'endDate'
                ]
            ])

            ->add('details', TextType::class, [
                'label' => 'Detalii: ',
                'attr' => [
                    'placeholder' => 'Detalii despre concediu...',
                    'class' => 'block w-auto rounded-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2'
                ]
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Trimite',
                'attr' => [
                    'class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Concedii::class,
        ]);
    }
}
