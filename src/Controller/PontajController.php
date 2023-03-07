<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Form\PontajeType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class PontajController extends AbstractController
{
    #[Route('/pontaje/new', name: 'app_pontaj_new')]
    public function addPontaj(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $form = $this->createForm(PontajeType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $pontaj = new Pontaje();
            $user = $security->getUser();

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

            $entityManager->persist($pontaj);
            $entityManager->flush();

            $this->addFlash('success', 'Pontajul a fost inregistrat!');

            return $this->redirectToRoute('app_pontaj');
        }

        return $this->render('pontaj/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/pontaje', name: 'app_pontaj')]
    public function index(Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        $pontaje = $entityManager->getRepository(Pontaje::class)->findBy(['user' => $user]);
        return $this->render('pontaj/index.html.twig', [
            'pontaje' => $pontaje
        ]);
    }

}
