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
                'label' => 'Început',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y H:i')),
            ])
            ->add('time_end', DateTimeType::class, [
                'label' => 'Sfârșit',
                'widget' => 'single_text',
                'data' => new DateTime($date->format('d.m.Y H:i')),
            ])
            ->add('details', TextType::class, [
                'label' => 'Detalii',
                'attr' => ['placeholder' => 'ex: ședință, development, suport...'],
            ])
            ->add('Add', SubmitType::class, [
                'label' => 'Adaugă pontaj',
                'attr' => ['class' => 'btn btn-primary w-100 mt-2'],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Work::class,
        ]);
    }
}
