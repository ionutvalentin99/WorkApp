<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Form\PontajeType;
use App\Repository\PontajeRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/user')]
class PontajController extends AbstractController
{
    #[Route('/pontaje/new', name: 'app_pontaj_new')]
    public function addPontaj(Request $request, EntityManagerInterface $entityManager, Security $security, PontajeRepository $repository): Response
    {
        $form = $this->createForm(PontajeType::class);
        $form->handleRequest($request);
        $user = $security->getUser();
        $date = new DateTime();

        if($form->isSubmitted() && $form->isValid()){
            $pontaj = new Pontaje();
            $pontaj->setUser($user);
            $date = $form["date"]->getData();
            $time_start = $form["time_start"]->getData();
            $time_end = $form["time_end"]->getData();
            $details = $form["details"]->getData();
            $pontaj->setDate($date);
            $pontaj->setTimeStart($time_start);
            $pontaj->setTimeEnd($time_end);
            $pontaj->setCreated(new DateTime());
            $pontaj->setDetails($details);

            $repository->save($pontaj, true);

            $this->addFlash('success', 'Pontajul a fost inregistrat!');

            return $this->redirectToRoute('app_pontaj');
        }

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('pontaje')
            ->from('App:Pontaje', 'pontaje')
            ->where('pontaje.user = :user')
            ->setParameter('user', $user)
            ->andWhere('pontaje.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->andWhere('pontaje.time_end >= :time_end')
            ->setParameter('time_end', $date->format('H:i:s'))
            ->orderBy('pontaje.date', Criteria::ASC)
            ->addOrderBy('pontaje.time_start', Criteria::ASC);
        $qbData = $queryBuilder->getQuery()->getResult();

        return $this->render('pontaj/new.html.twig', [
            'form' => $form->createView(),
            'date' => $date->format('d-M-Y'),
            'pontaje' => $qbData,
        ]);
    }

    #[Route('/pontaje', name: 'app_pontaj')]
    public function index(EntityManagerInterface $entityManager, Security $security): Response
    {
        $date = new DateTime();

        $user = $security->getUser();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('pontaje')
            ->from('App:Pontaje', 'pontaje')
            ->where('pontaje.user = :user')
            ->setParameter('user', $user)
            ->andWhere('pontaje.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->andWhere('pontaje.time_end >= :time_end')
            ->setParameter('time_end', $date->format('H:i:s'))
            ->orderBy('pontaje.date', Criteria::ASC)
            ->addOrderBy('pontaje.time_start', Criteria::ASC);

        $qbData = $queryBuilder->getQuery()->getResult();

        return $this->render('pontaj/index.html.twig', [
            'pontaje' => $qbData,
            'date' => $date->format('d-M-Y')
        ]);
    }
    #[Route('/pontaje/delete/{id}', name: 'app_pontaj_delete')]
    public function delete(Pontaje $pontaje, PontajeRepository $repository): Response
    {
        $repository->remove($pontaje, true);
        $this->addFlash('danger', 'Pontajul a fost sters!');

        return $this->redirectToRoute('app_pontaj');
    }
    #[Route('/pontaje/update/{id}', name: 'app_pontaj_update')]
    public function edit(Request $request, Pontaje $pontaje, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PontajeType::class, $pontaje);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

        $time_end = $form["time_end"]->getData();
        $time_start = $form["time_start"]->getData();
        $details = $form["details"]->getData();
        $date = $form["date"]->getData();
        $pontaje->setDate($date);
        $pontaje->setTimeStart($time_start);
        $pontaje->setTimeEnd($time_end);
        $pontaje->setDetails($details);
        $pontaje->setUpdated(new DateTime());

        $entityManager->flush();

        $this->addFlash('success', 'Pontajul a fost actualizat!');
        return $this->redirectToRoute('app_pontaj');
        }

        return $this->render('pontaj/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/pontaje/all', name: 'app_pontaj_showAll')]
    public function showAll(Security $security, EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $security->getUser();
        $form = $this->createFormBuilder()
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Alegeți data: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Căutare',
                'attr' => ['class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800']
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $qb = $entityManager->createQueryBuilder();
            $qb->select('p')
                ->from('App:Pontaje', 'p')
                ->where('p.user = :user')
                ->setParameter('user', $user)
                ->andWhere('p.date = :date')
                ->setParameter('date', $form["date"]->getData());

            $qbData = $qb->getQuery()->getResult();
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

//        Numar elemente per pagina:
        $perPage = 20;

        $pagination = $paginator->paginate(
            $qbData,
            $request->query->getInt('page', 1),
            $perPage
        );

        return $this->render('pontaj/showAll.html.twig', [
            'pontaje' => $qbData,
            'form' => $form->createView(),
            'pagination' => $pagination,
        ]);
    }

}
