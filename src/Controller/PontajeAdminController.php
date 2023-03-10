<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Form\PontajeType;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PontajeAdminController extends AbstractController
{
    #[Route('/admin/pontaje', name: 'app_pontaje_admin')]
    public function showPontaje(EntityManagerInterface $entityManager): Response
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('pontaje')
            ->from('App:Pontaje', 'pontaje')
            ->orderBy('pontaje.date', Criteria::DESC)
            ->addOrderBy('pontaje.time_end', Criteria::DESC);

        $qbData = $queryBuilder->getQuery()->getResult();

        return $this->render('pontaje_admin/index.html.twig', [
            'pontaje' => $qbData
        ]);
    }

    #[Route('/admin/pontaje/delete/{id}', name: 'app_pontaj_admin_delete')]
    public function deletePontaj(Pontaje $pontaje, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($pontaje);
        $entityManager->flush();

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
