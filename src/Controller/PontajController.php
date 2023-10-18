<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Entity\User;
use App\Form\PontajeType;
use App\Repository\PontajeRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/user')]
class PontajController extends AbstractController
{
    public function __construct(
        private readonly PontajeRepository $pontajeRepository)
    {
    }

    #[Route('/pontaje', name: 'app_pontaj')]
    public function index(): Response
    {
        $date = new DateTime();
        /** @var User $user */
        $user = $this->getUser();
        $pontaje = $this->pontajeRepository->getActivePontaje($user);

        return $this->render('pontaj/index.html.twig', [
            'pontaje' => $pontaje,
            'date' => $date->format('d-M-Y'),
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/pontaje/new', name: 'app_pontaj_new')]
    public function addPontaj(Request $request): Response
    {
        $form = $this->createForm(PontajeType::class);
        $form->handleRequest($request);
        /** @var User $user */
        $user = $this->getUser();
        $date = new DateTime();

        if ($form->isSubmitted() && $form->isValid()) {
            $time_start = $form["time_start"]->getData();
            $time_end = $form["time_end"]->getData();
            $details = $form["details"]->getData();

            if ($time_start < $time_end) {
                $pontaj = new Pontaje();
                $pontaj->setUser($user);
                $pontaj->setTimeStart($time_start);
                $pontaj->setDate($time_start);
                $pontaj->setTimeEnd($time_end);
                $pontaj->setCreated(new DateTime());
                $pontaj->setDetails($details);
            }
            else
            {
                throw new InvalidArgumentException('Start time must be lower than end time!');
            }

            $this->pontajeRepository->save($pontaj, true);

            $this->addFlash('success', 'Pontajul a fost inregistrat!');

            return $this->redirectToRoute('app_pontaj');
        }

        $pontaje = $this->pontajeRepository->getActivePontaje($user);

        return $this->render('pontaj/new.html.twig', [
            'form' => $form->createView(),
            'date' => $date->format('d-M-Y'),
            'pontaje' => $pontaje,
        ]);
    }

    #[Route('/pontaje/update/{id}', name: 'app_pontaj_update')]
    public function edit(Request $request, Pontaje $pontaje, EntityManagerInterface $entityManager): Response
    {
        try {
            $form = $this->createForm(PontajeType::class, $pontaje);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $time_end = $form["time_end"]->getData();
                $time_start = $form["time_start"]->getData();
                $details = $form["details"]->getData();
                $pontaje->setTimeStart($time_start);
                $pontaje->setTimeEnd($time_end);
                $pontaje->setDate($time_start);
                $pontaje->setDetails($details);
                $pontaje->setUpdated(new DateTime());

                $entityManager->flush();

                $this->addFlash('success', 'Record has been updated!');
                return $this->redirectToRoute('app_pontaj');
            }
        } catch (Exception $e) {
            dd($e);
        }

        return $this->render('pontaj/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @throws Exception
     */
    #[Route('/pontaje/all', name: 'app_pontaj_showAll')]
    public function showAll(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Day Search: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Search',
                'attr' => ['class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800']
            ])
            ->setMethod('GET')
            ->getForm();

        $formMonth = $this->createFormBuilder()
            ->add('from', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Interval Search from: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3']
            ])
            ->add('to', DateType::class, [
                'widget' => 'single_text',
                'label' => 'to: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Search',
                'attr' => ['class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800']
            ])
            ->setMethod('GET')
            ->getForm();

        $form->handleRequest($request);
        $formMonth->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $date = $form["date"]->getData();

            $qb = $entityManager->createQueryBuilder();
            $qb->select('p')
                ->from('App:Pontaje', 'p')
                ->where('p.user = :user')
                ->setParameter('user', $user)
                ->andWhere('p.date = :date')
                ->setParameter('date', $date);

            $qbData = $qb->getQuery()->getResult();
        }
        elseif ($formMonth->isSubmitted() && $formMonth->isValid())
        {
            if($formMonth["from"]->getData() <= $formMonth["to"]->getData())
            {
                $qb = $entityManager->createQueryBuilder();
                $qb->select('p')
                    ->from('App:Pontaje', 'p')
                    ->where('p.user = :user')
                    ->setParameter('user', $user)
                    ->andWhere('p.date >= :from')
                    ->setParameter('from', $formMonth["from"]->getData())
                    ->andWhere('p.date <= :to')
                    ->setParameter('to', $formMonth["to"]->getData())
                    ->orderBy('p.date', Criteria::DESC)
                    ->addOrderBy('p.time_end', Criteria::DESC);

                $qbData = $qb->getQuery()->getResult();
            }
            else
            {
                throw new InvalidArgumentException('Start Date must be lower than End Date or equal!');
            }

        }
        else
        {
            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('p')
                ->from('App:Pontaje', 'p')
                ->where('p.user = :user')
                ->setParameter('user', $user)
                ->orderBy('p.date', Criteria::DESC)
                ->addOrderBy('p.time_end', Criteria::DESC);

            $qbData = $queryBuilder->getQuery()->getResult();
        }

        $totalCount = count($qbData);

        $perPage = 5;

        $pagination = $paginator->paginate(
            $qbData,
            $request->query->getInt('page', 1),
            $perPage,
        );

        return $this->render('pontaj/showAll.html.twig', [
            'pontaje' => $qbData,
            'form' => $form->createView(),
            'formMonth' => $formMonth->createView(),
            'entryNumber' => $totalCount,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/pontaje/delete/{id}', name: 'app_pontaj_delete')]
    public function delete(Pontaje $pontaje): Response
    {
        $this->pontajeRepository->remove($pontaje, true);
        $this->addFlash('danger', 'Record has been deleted!');

        return $this->redirectToRoute('app_pontaj');
    }

}