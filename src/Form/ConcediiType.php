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
                'label' => 'Due',
                'attr' => [
                    'class' => 'block dark:text-black',
                    'name' => 'startDate'
                ]
            ])

            ->add('endDate', DateType::class, [
                'mapped' => true,
                'widget' => 'single_text',
                'data' => new DateTime(),
                'label' => 'To',
                'attr' => [
                    'class' => 'block dark:text-black',
                    'name' => 'endDate'
                ]
            ])

            ->add('details', TextType::class, [
                'label' => 'Detalii: ',
                'attr' => [
                    'placeholder' => 'Detalii despre concediul dorit...',
                    'class' => 'block w-full p-4 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'
                ]
            ])

            ->add('Trimite', SubmitType::class, [
                'attr' => [
                    'class' => 'block w-full shadow-sm border-transparent bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white rounded-md border p-2 mt-4 mb-2'
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
