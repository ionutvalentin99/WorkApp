<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/company')]
class CompanyController extends AbstractController
{
    #[Route('/', name: 'app_company')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (null === $user->getCompany()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('company/index.html.twig', [
            'company' => $user->getCompany(),
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CompanyRepository $companyRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (null !== $user->getCompany()) {
            return $this->redirectToRoute('app_home');
        }
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setOwner($user);
            $company->setIsPaid(false);
            $user->setCompany($company);
            $companyRepository->save($company, true);

            return $this->redirectToRoute('app_stripe_checkout');
        }

        return $this->render('company/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/change-name', name: 'app_company_change_name', methods: ['GET', 'POST'])]
    public function changeName(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        $companyNewName = $request->request->get('company-name');
        if (!$companyNewName) {
            throw new BadRequestHttpException('New name is required.');
        }

        $company->setName($companyNewName);
        $entityManager->flush();

        return $this->redirectToRoute('app_company');
    }

    #[Route('/delete-account', name: 'app_delete_company', methods: ['POST'])]
    public function deleteAccount(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        if ($company) {
            $user->setCompany(null);

            $em->remove($company);
            $em->flush();
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/leave-company', name: 'app_leave_company', methods: ['POST'])]
    public function leaveCompany(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        if ($company->getOwner() !== $user) {
            $user->setCompany(null);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_home', [
            "error" => "You can't leave this company, you are the owner! You can remove the company instead.",
        ]);
    }
}