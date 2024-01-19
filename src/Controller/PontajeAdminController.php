<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Form\PontajeType;
use App\Repository\PontajeRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PontajeAdminController extends AbstractController
{
    #[Route('/admin/pontaje', name: 'app_pontaje_admin')]
    public function showPontaje(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createFormBuilder()
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Alegeti ziua: ',
                'data' => new DateTime(),
                'attr' => ['class' => 'rounded-full text-black hover:focus:ring-3 dark:hover:bg-gray-200 top-3'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Căutare',
                'attr' => ['class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800']
            ])
            ->setMethod('GET')
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $qb = $entityManager->createQueryBuilder();
            $qb->select('p')
                ->from('App:Pontaje', 'p')
                ->where('p.date = :date')
                ->setParameter('date', $form["date"]->getData());

            $qbData = $qb->getQuery()->getResult();
        }
        else {
            $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('pontaje')
                ->from('App:Pontaje', 'pontaje')
                ->orderBy('pontaje.date', Criteria::DESC)
                ->addOrderBy('pontaje.time_end', Criteria::DESC);

            $qbData = $queryBuilder->getQuery()->getResult();
        }

        $perPage = 10;
        $paginate = $paginator->paginate(
            $qbData,
           $request->query->getInt('page', 1),
           $perPage
        );

        return $this->render('pontaje_admin/index.html.twig', [
            'pontaje' => $qbData,
            'pagination' => $paginate,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/pontaje/delete/{id}', name: 'app_pontaj_admin_delete')]
    public function deletePontaj(Pontaje $pontaje, PontajeRepository $repository): Response
    {
        $repository->remove($pontaje, true);

        $this->addFlash('danger', 'Pontajul a fost sters!');
        return $this->redirectToRoute('app_pontaje_admin');
    }

    #[Route('/admin/pontaje/update/{id}', name: 'app_pontaje_admin_edit')]
    public function edit(Request $request, Pontaje $pontaje, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PontajeType::class, $pontaje);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $time_start = $form["time_start"]->getData();
            $time_end = $form["time_end"]->getData();
            $details = $form["details"]->getData();
            $date = $form["date"]->getData();
            $pontaje->setDate($date);
            $pontaje->setTimeStart($time_start);
            $pontaje->setTimeEnd($time_end);
            $pontaje->setUpdated(new DateTime());
            $pontaje->setDetails($details);

            $entityManager->flush();

            $this->addFlash('success', 'Pontajul a fost actualizat!');

            return $this->redirectToRoute('app_pontaje_admin');
        }

        return $this->render('pontaje_admin/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }

}
