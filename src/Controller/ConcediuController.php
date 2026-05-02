<?php

namespace App\Controller;

use App\Entity\Holiday;
use App\Entity\User;
use App\Form\ConcediiType;
use App\Repository\HolidayRepository;
use App\Service\ActiveCompanyService;
use App\Service\NotificationService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

class ConcediuController extends AbstractController
{
    public function __construct(
        private readonly HolidayRepository   $repository,
        private readonly PaginatorInterface  $paginator,
        private readonly NotificationService $notificationService,
        private readonly ActiveCompanyService $activeCompanyService,
    ) {}

    #[Route('/user/vacation', name: 'app_concediu', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('concediu/index.html.twig');
    }
    #[Route('/user/vacation/new-vacation', name: 'app_concediu_new', methods: ['GET', 'POST'])]
    public function addConcediu(
        Request           $request,
    ): Response
    {
        $form = $this->createForm(ConcediiType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Holiday $concediu */
            /** @var User $user */
            $user = $this->getUser();

            $concediu = new Holiday();
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

            $activeCompany = $this->activeCompanyService->getActiveCompany();
            if ($activeCompany) {
                $owner = $activeCompany->getOwner();
                if ($owner && $owner !== $user) {
                    $this->notificationService->notify(
                        $owner,
                        sprintf('%s %s a trimis o cerere de concediu (%s - %s).',
                            $user->getFirstName(),
                            $user->getLastName(),
                            $startDate->format('d.m.Y'),
                            $endDate->format('d.m.Y')
                        ),
                        'info',
                        $this->generateUrl('app_pending_concedii')
                    );
                }
            }

            $this->addFlash('success', 'Cererea ta a fost trimisă cu succes!');

            return $this->redirectToRoute('app_concediu');
        }
        return $this->render('concediu/new.html.twig', [
            'form' => $form->createView()
        ]);

    }
    #[Route('/user/vacation/history', name: 'app_concediu_showconcedii', methods: ['GET'])]
    public function showConcedii(
        Request            $request,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userHolidays = $this->repository->getAllHolidaysDesc($user);

        $perPage = 5;
        $pagination = $this->paginator->paginate(
            $userHolidays,
            $request->query->getInt('page', 1),
            $perPage,
        );

        return $this->render('concediu/show.html.twig', [
            'concedii' => $userHolidays,
            'pagination' => $pagination,
        ]);
    }
    #[Route('/uservacation/{id}/response', name: 'app_concediu_detailed', methods: ['GET'])]
    public function concediuDetailed(Holiday $id): Response
    {
        return $this->render('concediu/concediuDetailed.html.twig', [
            'concedii' => $id
        ]);
    }
}
