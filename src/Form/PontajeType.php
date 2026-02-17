<?php

namespace App\Form;

use App\Entity\Work;
use App\Repository\WorkRepository;
use DateTime;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PontajeType extends AbstractType
{
    public function __construct(private readonly WorkRepository $pontajeRepository, private readonly Security $security)
    {
    }

    /**
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lastInsert = $this->pontajeRepository->getLastInsertByUser($this->security->getUser());
        $currentDate = new DateTime('now');

        $date = (empty($lastInsert) || $lastInsert[0]['time_end'] < $currentDate) ? new DateTime('now') : $lastInsert[0]['time_end'];

        $builder
            ->add('time_start', DateTimeType::class, [
                'mapped' => true,
                'label' => 'Start at:',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y H:i')),
                'attr' => [
                    'class' => 'w-full p-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:border-blue-500 focus:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:bg-gray-800 dark:focus:border-blue-500 transition-all duration-300',
                    'placeholder' => 'Select start time',
                    'name' => 'time_start'
                ]
            ])
            ->add('time_end', DateTimeType::class, [
                'mapped' => true,
                'label' => 'End at:',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y H:i')),
                'attr' => [
                    'class' => 'w-full p-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:border-blue-500 focus:bg-gray-50 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:bg-gray-800 dark:focus:border-blue-500 transition-all duration-300',
                    'placeholder' => 'Select end time',
                    'name' => 'time_end'
                ]
            ])
            ->add('details', TextType::class, [
                'label' => 'Details:',
                'attr' => [
                    'class' => 'w-full p-3 rounded-lg shadow-sm border-2 border-gray-300 focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:bg-gray-800 dark:focus:border-blue-500 transition-all duration-300',
                    'placeholder' => 'Record details...',
                ]
            ])
            ->add('Add', SubmitType::class, [
                'attr' => [
                    'class' => 'w-full text-white mt-4 bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 transition-all duration-300 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-blue-800'
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Work::class,
        ]);
    }
}
