<?php

namespace App\Form;

use App\Entity\Pontaje;
use App\Repository\PontajeRepository;
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
    public function __construct(private readonly PontajeRepository $pontajeRepository, private readonly Security $security)
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
                'label' => 'Început la: ',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y H:i')),
                'attr' => [
                    'class' => 'block dark:text-black rounded-full',
                    'name' => 'time_start'
                ]
            ])
            ->add('time_end', DateTimeType::class, [
                'mapped' => true,
                'label' => 'Sfârșit la: ',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y H:i')),
                'attr' => [
                    'class' => 'block dark:text-black rounded-full',
                    'name' => 'time_end'
                ]
            ])
            ->add('details', TextType::class, [
                'label' => 'Detalii: ',
                'attr' => [
                    'class' => 'block w-auto rounded-full shadow-sm border-gray-300 dark:border-transparent dark:text-gray-800 rounded-md border p-2 mt-1 mb-2',
                    'placeholder' => 'Detalii pontaj...',

                ]
            ])
            ->add('Trimite', SubmitType::class, [
                'attr' => [
                    'class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pontaje::class,
        ]);
    }
}
