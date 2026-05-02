<?php

namespace App\Form;

use App\Entity\Holiday;
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
                'widget' => 'single_text',
                'data' => new DateTime(),
                'label' => 'De la',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'data' => new DateTime(),
                'label' => 'Până la',
            ])
            ->add('details', TextType::class, [
                'label' => 'Detalii',
                'attr' => ['placeholder' => 'ex: concediu medical, odihnă, eveniment personal...'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Trimite cererea',
                'attr' => ['class' => 'btn btn-primary w-100 mt-2'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Holiday::class,
        ]);
    }
}
