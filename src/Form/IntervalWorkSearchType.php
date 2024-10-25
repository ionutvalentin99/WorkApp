<?php

namespace App\Form;

use App\Entity\Pontaje;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class IntervalWorkSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'label' => 'Interval Search from: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3']
            ])
            ->add('dateTo', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'label' => 'to: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Search',
                'attr' => ['class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800']
            ])
            ->setMethod('GET');
    }

    public function validate($value, ExecutionContextInterface $context): void
    {
        $form = $context->getRoot();
        $dateFrom = $form->get('dateFrom')->getData();
        $dateTo = $form->get('dateTo')->getData();

        if ($dateFrom > $dateTo) {
            $context
                ->buildViolation('Start Date must be lower than or equal to End Date.')
                ->atPath('dateTo')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pontaje::class,
            'constraints' => [
                new Callback($this->validate(...))
            ]
        ]);
    }
}
