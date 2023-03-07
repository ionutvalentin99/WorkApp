<?php

namespace App\Form;

use App\Entity\Pontaje;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PontajeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class,  [
                'mapped' => true,
                'widget' => 'single_text',
                'data' => new DateTime(),
                'label' => 'Date: ',
                'attr' => [
                    'class' => 'block dark:text-black',
                    'name' => 'date'
                ]
            ])
            ->add('time_start', TimeType::class, [
                'mapped' => true,
                'label' => 'Start Time: ',
                'attr' => [
                    'class' => 'block dark:text-black',
                    'name' => 'time_start'
                    ]
            ])
            ->add('time_end', TimeType::class, [
                'mapped' => true,
                'label' => 'End Time: ',
                'attr' => [
                    'class' => 'block dark:text-black',
                    'name' => 'time_end'
                ]
            ])
            ->add('details', TextType::class, [
                'label' => 'Detalii: ',
                'attr' => [
                    'class' => 'block w-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2',
                    'placeholder' => 'Detalii pontaj...',

                ]
            ])
            ->add('Trimite', SubmitType::class, [
                'attr' => [
                    'class' => 'block w-full shadow-sm border-transparent bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 text-white rounded-md border p-2 mt-4 mb-2'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pontaje::class,
        ]);
    }
}
