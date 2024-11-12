<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Entity\User;
use App\Form\DailyWorkSearchType;
use App\Form\IntervalWorkSearchType;
use App\Form\PontajeType;
use App\Repository\PontajeRepository;
use App\Service\UuidService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/user')]
class PontajController extends AbstractController
{
    #[Route('/pontaje', name: 'app_pontaj')]
    public function index(PontajeRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }

        $pontaje = $repository->getActivePontaje($user, $user->getCompany());

        return $this->render('pontaj/index.html.twig', [
            'pontaje' => $pontaje,
            'date' => (new DateTime())->format('d-M-Y'),
        ]);
    }

    #[Route('/pontaje/all', name: 'app_pontaj_showAll')]
    public function showAll(Request $request, PaginatorInterface $paginator, PontajeRepository $repository): Response
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

        $qbData = $repository->getDefaultEntries($user, $user->getCompany());
        if ($form->isSubmitted() && $form->isValid()) {
            $qbData = $repository->getSingleDaySearchResult($user, $user->getCompany(), $form["date"]->getData());
        } elseif ($formMonth->isSubmitted() && $formMonth->isValid() && $formMonth["dateFrom"]->getData() <= $formMonth["dateTo"]->getData()) {
            $qbData = $repository->getIntervalSearchLessThan($user, $user->getCompany(), $formMonth["dateFrom"]->getData(), $formMonth["dateTo"]->getData());
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

    #[Route('/pontaje/new', name: 'app_pontaj_new')]
    public function addPontaj(Request $request, PontajeRepository $repository, UuidService $uuid): Response
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
                $pontaj = new Pontaje();
                $pontaj->setUser($user);
                $pontaj->setTimeStart($time_start);
                $pontaj->setDate($time_start);
                $pontaj->setTimeEnd($time_end);
                $pontaj->setCreated(new DateTime());
                $pontaj->setDetails($details);
                $pontaj->setRecordId($uuid->getUuid());
                $pontaj->setCompany($user->getCompany());

                $repository->save($pontaj, true);
                $this->addFlash('success', 'Work has been confirmed!');

                return $this->redirectToRoute('app_pontaj');
            } else {
                $this->addFlash('danger', 'Start date must be lower than end date!');

                return $this->redirectToRoute('app_pontaj_new');
            }
        }
        $pontaje = $repository->getActivePontaje($user, $user->getCompany());

        return $this->render('pontaj/new.html.twig', [
            'form' => $form->createView(),
            'date' => (new DateTime('now'))->format('d-M-Y'),
            'pontaje' => $pontaje,
        ]);
    }

    #[Route('/pontaje/update/{id}', name: 'app_pontaj_update')]
    public function edit($id, Request $request, Pontaje $pontaje, EntityManagerInterface $entityManager, PontajeRepository $workRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->isEnrolled()) {
            return $this->redirectToRoute('app_company_new');
        }
        $work = $workRepository->find($id);
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

    #[Route('/pontaje/delete/{id}', name: 'app_pontaj_delete')]
    public function delete($id, Pontaje $pontaje, PontajeRepository $workRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $work = $workRepository->find($id);
        if ($work->getUser()->getId() !== $user->getId()) {
            throw new NotFoundHttpException('Access denied.');
        }

        $workRepository->remove($pontaje, true);
        $this->addFlash('danger', 'Record has been deleted!');

        return $this->redirectToRoute('app_pontaj');
    }
}