<?php

namespace App\Controller;

use App\Entity\Concedii;
use App\Entity\User;
use App\Form\ConcediiType;
use App\Repository\ConcediiRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

#[Route('/user')]
class ConcediuController extends AbstractController
{
    #[Route('/concedii', name: 'app_concediu', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('concediu/index.html.twig');
    }

    #[Route('/concedii/new', name: 'app_concediu_new', methods: ['GET', 'POST'])]
    public function addConcediu(
        Request            $request,
        ConcediiRepository $repository,
    ): Response
    {
        $form = $this->createForm(ConcediiType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Concedii $concediu */
            /** @var User $user */
            $user = $this->getUser();

            $concediu = new Concedii();
            $concediu->setUserId($user);
            $startDate = $form["startDate"]->getData();
            $endDate = $form["endDate"]->getData();
            $details = $form["details"]->getData();
            $concediu->setStartDate($startDate);
            $concediu->setEndDate($endDate);
            $concediu->setDetails($details);
            $concediu->setCreated(new DateTime());
            $concediu->setStatus('pending');

            $repository->save($concediu, true);

            $this->addFlash('success', 'Your request has been sent!');

            return $this->redirectToRoute('app_concediu');
        }
        return $this->render('concediu/new.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/concedii/concediile-tale', name: 'app_concediu_showconcedii', methods: ['GET'])]
    public function showConcedii(
        ConcediiRepository $repository,
        PaginatorInterface $paginator,
        Request            $request,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userHolidays = $repository->getAllHolidaysDesc($user);

        $perPage = 5;
        $pagination = $paginator->paginate(
            $userHolidays,
            $request->query->getInt('page', 1),
            $perPage,
        );

        return $this->render('concediu/show.html.twig', [
            'concedii' => $userHolidays,
            'pagination' => $pagination,
        ]);
    }

    #[Route('concedii/{id}/response', name: 'app_concediu_detailed', methods: ['GET'])]
    public function concediuDetailed(Concedii $id): Response
    {
        return $this->render('concediu/concediuDetailed.html.twig', [
            'concedii' => $id
        ]);
    }
}
