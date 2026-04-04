<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\CompanyRequest;
use App\Entity\User;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\CompanyRequestRepository;
use App\Repository\UserRepository;
use App\Service\ActiveCompanyService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class CompanyController extends AbstractController
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ActiveCompanyService $activeCompanyService,
        private readonly NotificationService $notificationService,
    ) {}

    #[Route('/user/company/', name: 'app_company')]
    public function index(): Response
    {
        $activeCompany = $this->activeCompanyService->getActiveCompany();
        if (null === $activeCompany) {
            $this->addFlash('warning', 'Creați o companie sau înregistrați-vă într-una!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('company/index.html.twig', [
            'company' => $activeCompany,
        ]);
    }

    #[Route('/user/company/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setOwner($user);
            $company->setIsPaid(false);
            $company->addUser($user);
            $this->companyRepository->save($company, true);
            $this->activeCompanyService->setActiveCompany($company);

            return $this->redirectToRoute('app_stripe_checkout');
        }

        return $this->render('company/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/user/company/change-name', name: 'app_company_change_name', methods: ['GET', 'POST'])]
    public function changeName(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();
        if (!$company || $company->getOwner() !== $user) {
            throw new BadRequestHttpException('Access denied.');
        }

        $companyNewName = $request->request->get('company-name');
        if (!$companyNewName) {
            throw new BadRequestHttpException('New name is required.');
        }

        $company->setName($companyNewName);
        $entityManager->flush();

        return $this->redirectToRoute('app_company');
    }

    #[Route('/user/company/delete', name: 'app_delete_company', methods: ['POST'])]
    public function deleteCompany(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();
        if ($company && $company->getOwner() === $user) {
            $this->activeCompanyService->clearActiveCompany();
            $em->remove($company);
            $em->flush();
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/user/company/leave-company', name: 'app_leave_company', methods: ['POST'])]
    public function leaveCompany(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();

        if ($company && $company->getOwner() !== $user) {
            $company->removeUser($user);
            $this->activeCompanyService->clearActiveCompany();
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('danger', 'Nu poți părăsi compania dacă ești owner-ul ei. Poți șterge compania în schimb.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/user/company/switch/{id}', name: 'app_company_switch', methods: ['POST'])]
    public function switchCompany(Company $company): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getCompanies()->contains($company)) {
            $this->activeCompanyService->setActiveCompany($company);
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/user/company/search', name: 'app_company_search', methods: ['GET'])]
    public function search(Request $request, CompanyRepository $companyRepo, CompanyRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $query = $request->query->get('q');
        $companies = [];

        if ($query) {
            $companies = $companyRepo->createQueryBuilder('c')
                ->where('c.name LIKE :query')
                ->andWhere('c.is_searchable = true')
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        }

        $pendingRequests = $requestRepo->findBy(['user' => $user, 'status' => 'PENDING']);
        $requestedCompanyIds = array_map(fn($req) => $req->getCompany()->getId(), $pendingRequests);

        $memberCompanyIds = $user->getCompanies()->map(fn($c) => $c->getId())->toArray();

        return $this->render('company/search.html.twig', [
            'companies' => $companies,
            'query' => $query,
            'requestedCompanyIds' => $requestedCompanyIds,
            'memberCompanyIds' => $memberCompanyIds,
        ]);
    }

    #[Route('/user/company/join/{id}', name: 'app_company_join', methods: ['POST'])]
    public function join(Company $company, EntityManagerInterface $em, CompanyRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getCompanies()->contains($company)) {
            $this->addFlash('info', 'Ești deja membru în această companie.');
            return $this->redirectToRoute('app_company_search');
        }

        if ($requestRepo->hasPendingRequest($user, $company)) {
            $this->addFlash('warning', 'Ai trimis deja o cerere către această companie.');
            return $this->redirectToRoute('app_company_search');
        }

        $joinRequest = new CompanyRequest();
        $joinRequest->setUser($user);
        $joinRequest->setCompany($company);
        $joinRequest->setType('JOIN_REQUEST');

        $em->persist($joinRequest);
        $em->flush();

        // Notificăm ownerul companiei
        $this->notificationService->notify(
            $company->getOwner(),
            sprintf('%s %s dorește să se alăture companiei %s.', $user->getFirstName(), $user->getLastName(), $company->getName()),
            'info',
            $this->generateUrl('app_company_requests')
        );

        $this->addFlash('success', 'Cererea de alăturare către ' . $company->getName() . ' a fost trimisă cu succes!');
        return $this->redirectToRoute('app_company_search');
    }

    // -------------------------------------------------------------------------
    // LISTA ȘI GESTIONARE MEMBRI
    // -------------------------------------------------------------------------

    #[Route('/user/company/members', name: 'app_company_members', methods: ['GET'])]
    public function members(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();

        if (!$company) {
            return $this->redirectToRoute('app_home');
        }

        if ($company->getOwner() !== $user) {
            $this->addFlash('danger', 'Doar ownerul poate gestiona membrii companiei.');
            return $this->redirectToRoute('app_company');
        }

        return $this->render('company/members.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/user/company/members/{id}/remove', name: 'app_company_member_remove', methods: ['POST'])]
    public function removeMember(User $member, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();

        if (!$company || $company->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($member === $user) {
            $this->addFlash('danger', 'Nu te poți elimina pe tine însuți din companie.');
            return $this->redirectToRoute('app_company_members');
        }

        $company->removeUser($member);
        $em->flush();

        $this->notificationService->notify(
            $member,
            sprintf('Ai fost eliminat din compania %s.', $company->getName()),
            'warning'
        );

        $this->addFlash('success', sprintf('%s %s a fost eliminat din companie.', $member->getFirstName(), $member->getLastName()));
        return $this->redirectToRoute('app_company_members');
    }

    // -------------------------------------------------------------------------
    // GESTIONARE CERERI (owner vede și răspunde la cereri de join)
    // -------------------------------------------------------------------------

    #[Route('/user/company/requests', name: 'app_company_requests', methods: ['GET'])]
    public function requests(CompanyRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();

        if (!$company || $company->getOwner() !== $user) {
            $this->addFlash('danger', 'Doar ownerul companiei poate vedea cererile.');
            return $this->redirectToRoute('app_home');
        }

        $pendingRequests = $requestRepo->findPendingRequestsForCompany($company);

        return $this->render('company/requests.html.twig', [
            'company' => $company,
            'requests' => $pendingRequests,
        ]);
    }

    #[Route('/user/company/requests/{id}/accept', name: 'app_company_request_accept', methods: ['POST'])]
    public function acceptRequest(CompanyRequest $companyRequest, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $companyRequest->getCompany();

        if ($company->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $requestedUser = $companyRequest->getUser();
        $company->addUser($requestedUser);
        $companyRequest->setStatus('ACCEPTED');
        $em->flush();

        // Notificăm userul că a fost acceptat
        $this->notificationService->notify(
            $requestedUser,
            sprintf('Cererea ta de alăturare la compania %s a fost acceptată!', $company->getName()),
            'success',
            $this->generateUrl('app_company')
        );

        $this->addFlash('success', sprintf('%s %s a fost adăugat în companie.', $requestedUser->getFirstName(), $requestedUser->getLastName()));
        return $this->redirectToRoute('app_company_requests');
    }

    #[Route('/user/company/requests/{id}/reject', name: 'app_company_request_reject', methods: ['POST'])]
    public function rejectRequest(CompanyRequest $companyRequest, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $companyRequest->getCompany();

        if ($company->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $requestedUser = $companyRequest->getUser();
        $companyRequest->setStatus('REJECTED');
        $em->flush();

        // Notificăm userul că a fost respins
        $this->notificationService->notify(
            $requestedUser,
            sprintf('Cererea ta de alăturare la compania %s a fost respinsă.', $company->getName()),
            'warning'
        );

        $this->addFlash('info', sprintf('Cererea lui %s %s a fost respinsă.', $requestedUser->getFirstName(), $requestedUser->getLastName()));
        return $this->redirectToRoute('app_company_requests');
    }

    // -------------------------------------------------------------------------
    // INVITAȚII (owner invită un user)
    // -------------------------------------------------------------------------

    #[Route('/user/company/invite', name: 'app_company_invite', methods: ['GET'])]
    public function invite(Request $request, UserRepository $userRepo, CompanyRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();

        if (!$company || $company->getOwner() !== $user) {
            $this->addFlash('danger', 'Doar ownerul companiei poate trimite invitații.');
            return $this->redirectToRoute('app_home');
        }

        $query = $request->query->get('q');
        $results = [];

        if ($query) {
            $results = $userRepo->createQueryBuilder('u')
                ->where('u.firstName LIKE :q OR u.lastName LIKE :q OR u.email LIKE :q OR u.username LIKE :q')
                ->andWhere('u.id != :ownerId')
                ->setParameter('q', '%' . $query . '%')
                ->setParameter('ownerId', $user->getId())
                ->setMaxResults(20)
                ->getQuery()
                ->getResult();
        }

        $memberIds = $company->getUsers()->map(fn($u) => $u->getId())->toArray();
        $pendingInvitations = $requestRepo->findBy(['company' => $company, 'status' => 'PENDING', 'type' => 'INVITATION']);
        $invitedUserIds = array_map(fn($req) => $req->getUser()->getId(), $pendingInvitations);

        return $this->render('company/invite.html.twig', [
            'company' => $company,
            'query' => $query,
            'results' => $results,
            'memberIds' => $memberIds,
            'invitedUserIds' => $invitedUserIds,
        ]);
    }

    #[Route('/user/company/invite/{id}', name: 'app_company_invite_send', methods: ['POST'])]
    public function sendInvitation(User $invitedUser, EntityManagerInterface $em, CompanyRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $this->activeCompanyService->getActiveCompany();

        if (!$company || $company->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($company->getUsers()->contains($invitedUser)) {
            $this->addFlash('info', 'Acest utilizator este deja membru în companie.');
            return $this->redirectToRoute('app_company_invite');
        }

        if ($requestRepo->hasPendingRequest($invitedUser, $company)) {
            $this->addFlash('warning', 'Există deja o cerere pendintă pentru acest utilizator.');
            return $this->redirectToRoute('app_company_invite');
        }

        $invitation = new CompanyRequest();
        $invitation->setUser($invitedUser);
        $invitation->setCompany($company);
        $invitation->setType('INVITATION');

        $em->persist($invitation);
        $em->flush();

        // Notificăm userul invitat
        $this->notificationService->notify(
            $invitedUser,
            sprintf('Ai primit o invitație să te alături companiei %s.', $company->getName()),
            'info',
            $this->generateUrl('app_invitations')
        );

        $this->addFlash('success', sprintf('Invitația a fost trimisă către %s %s.', $invitedUser->getFirstName(), $invitedUser->getLastName()));
        return $this->redirectToRoute('app_company_invite');
    }

    // -------------------------------------------------------------------------
    // INVITAȚII (user vede și răspunde la invitații primite)
    // -------------------------------------------------------------------------

    #[Route('/user/invitations', name: 'app_invitations', methods: ['GET'])]
    public function invitations(CompanyRequestRepository $requestRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $invitations = $requestRepo->findBy(['user' => $user, 'type' => 'INVITATION', 'status' => 'PENDING']);

        return $this->render('company/invitations.html.twig', [
            'invitations' => $invitations,
        ]);
    }

    #[Route('/user/invitations/{id}/accept', name: 'app_invitation_accept', methods: ['POST'])]
    public function acceptInvitation(CompanyRequest $invitation, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($invitation->getUser() !== $user || $invitation->getType() !== 'INVITATION') {
            throw $this->createAccessDeniedException();
        }

        $company = $invitation->getCompany();
        $company->addUser($user);
        $invitation->setStatus('ACCEPTED');
        $em->flush();

        // Notificăm ownerul că invitația a fost acceptată
        $this->notificationService->notify(
            $company->getOwner(),
            sprintf('%s %s a acceptat invitația și s-a alăturat companiei %s.', $user->getFirstName(), $user->getLastName(), $company->getName()),
            'success',
            $this->generateUrl('app_company')
        );

        $this->addFlash('success', sprintf('Te-ai alăturat companiei %s!', $company->getName()));
        return $this->redirectToRoute('app_invitations');
    }

    #[Route('/user/invitations/{id}/reject', name: 'app_invitation_reject', methods: ['POST'])]
    public function rejectInvitation(CompanyRequest $invitation, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($invitation->getUser() !== $user || $invitation->getType() !== 'INVITATION') {
            throw $this->createAccessDeniedException();
        }

        $company = $invitation->getCompany();
        $invitation->setStatus('REJECTED');
        $em->flush();

        // Notificăm ownerul că invitația a fost refuzată
        $this->notificationService->notify(
            $company->getOwner(),
            sprintf('%s %s a refuzat invitația la compania %s.', $user->getFirstName(), $user->getLastName(), $company->getName()),
            'warning'
        );

        $this->addFlash('info', sprintf('Ai refuzat invitația la compania %s.', $company->getName()));
        return $this->redirectToRoute('app_invitations');
    }
}
