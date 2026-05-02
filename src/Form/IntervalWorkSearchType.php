<?php

namespace App\Form;

use App\Entity\Work;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
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
                'label' => 'De la',
                'data' => new DateTime(),
            ])
            ->add('dateTo', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'label' => 'Până la',
                'data' => new DateTime(),
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Caută',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->setMethod(Request::METHOD_GET);
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
            'data_class' => Work::class,
            'constraints' => [
                new Callback($this->validate(...))
            ]
        ]);
    }
}
