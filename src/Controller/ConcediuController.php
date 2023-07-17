<?php

namespace App\Controller;

use App\Entity\Concedii;
use App\Entity\User;
use App\Form\ConcediiType;
use App\Repository\ConcediiRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

#[Route('/user')]
class ConcediuController extends AbstractController
{
    public function __construct(private readonly ConcediiRepository $repository, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/concedii', name: 'app_concediu', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('concediu/index.html.twig');
    }

    #[Route('/concedii/new', name: 'app_concediu_new', methods: ['GET', 'POST'])]
    public function addConcediu(Request $request): Response
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

            $this->repository->save($concediu, true);

            $this->addFlash('success', 'Cererea ta a fost trimisa la verificare!');

            return $this->redirectToRoute('app_concediu');
        }
        return $this->render('concediu/new.html.twig', [
            'form' => $form->createView()
        ]);

    }

    #[Route('/concedii/concediile-tale', name: 'app_concediu_showconcedii')]
    public function showConcedii(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from('App:Concedii', 'c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.start_date', Criteria::DESC);

        $queryData = $queryBuilder->getQuery()->getResult();

        return $this->render('concediu/show.html.twig',[
            'concedii' => $queryData,
            ]);
    }

    #[Route('concedii/{id}/response', name: 'app_concediu_detailed')]
    public function concediuDetailed(Concedii $id): Response
    {
        return $this->render('concediu/concediuDetailed.html.twig', [
            'concedii' => $id
        ]);
    }
}
