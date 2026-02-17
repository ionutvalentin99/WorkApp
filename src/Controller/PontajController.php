<?php

namespace App\Controller;

use App\Entity\Work;
use App\Entity\User;
use App\Form\DailyWorkSearchType;
use App\Form\IntervalWorkSearchType;
use App\Form\PontajeType;
use App\Repository\WorkRepository;
use App\Service\UuidService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

class PontajController extends AbstractController
{
    public function __construct(
        private readonly WorkRepository $repository,
        private readonly PaginatorInterface $paginator,
        private readonly UuidService $uuid,
        private readonly WorkRepository $workRepository)
    {
    }

    #[Route('/user/work', name: 'app_pontaj')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }
        $pontaje = $this->repository->getActivePontaje($user, $user->getCompany());
        return $this->render('pontaj/index.html.twig', [
            'pontaje' => $pontaje,
            'date' => (new DateTime())->format('d-M-Y'),
        ]);
    }

    #[Route('/user/work/your-work', name: 'app_pontaj_your_records')]
    public function showYourWork(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }
        $form = $this->createForm(DailyWorkSearchType::class);
        $formMonth = $this->createForm(IntervalWorkSearchType::class);

        $form->handleRequest($request);
        $formMonth->handleRequest($request);

        $qbData = $this->repository->getCompanyRecords($user->getCompany(), $user);
        if ($form->isSubmitted() && $form->isValid()) {
            $qbData = $this->repository->getCompanyRecords($user->getCompany(), $user, $form["date"]->getData());
        } elseif ($formMonth->isSubmitted() && $formMonth->isValid() && $formMonth["dateFrom"]->getData() <= $formMonth["dateTo"]->getData()) {
            $qbData = $this->repository->getCompanyRecords($user->getCompany(), $user, $formMonth["dateFrom"]->getData(), $formMonth["dateTo"]->getData());
        }

        $pagination = $this->paginator->paginate(
            $qbData,
            $request->query->getInt('page', 1),
            5 //items per page
        );

        return $this->render('pontaj/showAll.html.twig', [
            'pontaje' => $qbData,
            'form' => $form->createView(),
            'formMonth' => $formMonth->createView(),
            'entryNumber' => $pagination->getTotalItemCount(),
            'pagination' => $pagination,
        ]);
    }

    #[Route('/user/work/company-records', name: 'app_pontaj_company_records')]
    public function showCompanyWork(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }

        $form = $this->createForm(DailyWorkSearchType::class);
        $formMonth = $this->createForm(IntervalWorkSearchType::class);

        $form->handleRequest($request);
        $formMonth->handleRequest($request);

        $qbData = $this->repository->getCompanyRecords($user->getCompany());

        if ($form->isSubmitted() && $form->isValid()) {
            $qbData = $this->repository->getCompanyRecords($user->getCompany(), null, $form['date']->getData());
        } elseif ($formMonth->isSubmitted() && $formMonth->isValid() && $formMonth['dateFrom']->getData() <= $formMonth['dateTo']->getData()) {
            $qbData = $this->repository->getCompanyRecords($user->getCompany(), null, $formMonth['dateFrom']->getData(), $formMonth['dateTo']->getData());
        }

        $pagination = $this->paginator->paginate(
            $qbData,
            $request->query->getInt('page', 1),
            5 //items per page
        );

        return $this->render('pontaj/showAll.html.twig', [
            'pontaje' => $pagination->getItems(),
            'form' => $form->createView(),
            'formMonth' => $formMonth->createView(),
            'entryNumber' => $pagination->getTotalItemCount(),
            'pagination' => $pagination,
        ]);
    }

    #[Route('/user/work/new', name: 'app_pontaj_new')]
    public function addPontaj(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }
        $form = $this->createForm(PontajeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $time_start = $form["time_start"]->getData();
            $time_end = $form["time_end"]->getData();
            $details = $form["details"]->getData();

            if ($time_start < $time_end) {
                $pontaj = new Work();
                $pontaj->setUser($user);
                $pontaj->setTimeStart($time_start);
                $pontaj->setDate($time_start);
                $pontaj->setTimeEnd($time_end);
                $pontaj->setCreated(new DateTime());
                $pontaj->setDetails($details);
                $pontaj->setRecordId($this->uuid->getUuid());
                $pontaj->setCompany($user->getCompany());

                $this->repository->save($pontaj, true);
                $this->addFlash('success', 'Work has been confirmed!');

                return $this->redirectToRoute('app_pontaj');
            } else {
                $this->addFlash('danger', 'Start date must be lower than end date!');

                return $this->redirectToRoute('app_pontaj_new');
            }
        }
        $pontaje = $this->repository->getActivePontaje($user, $user->getCompany());

        return $this->render('pontaj/new.html.twig', [
            'form' => $form->createView(),
            'date' => (new DateTime('now'))->format('d-M-Y'),
            'pontaje' => $pontaje,
        ]);
    }

    #[Route('/user/work/update/{id}', name: 'app_pontaj_update')]
    public function edit($id, Request $request, Work $pontaje, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }
        $work = $this->workRepository->find($id);
        if ($work->getUser()->getId() !== $user->getId()) {
            throw new NotFoundHttpException('Access denied.');
        }

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

        return $this->render('pontaj/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/user/work/delete/{id}', name: 'app_pontaj_delete')]
    public function delete($id, Work $pontaje, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $work = $this->workRepository->find($id);
        if ($work->getUser()->getId() !== $user->getId()) {
            throw new NotFoundHttpException('Access denied.');
        }

        $this->workRepository->remove($pontaje, true);
        $this->addFlash('danger', 'Record has been deleted!');

        $previousRoute = $request->headers->get('referer');
        if ($previousRoute) {
            return $this->redirect($previousRoute);
        }

        return $this->redirectToRoute('app_pontaj');
    }
}