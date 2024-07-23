<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/company')]
class CompanyController extends AbstractController
{
    #[Route('/', name: 'app_company')]
    public function index(): Response
    {
        return $this->render('company/index.html.twig');
    }

    #[Route('/new', name: 'app_company_new')]
    public function new(Request $request, CompanyRepository $companyRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (null !== $user->getCompany()) {
            return $this->redirectToRoute('app_home');
        }
        $company = new Company();
        $company->setOwner($user);
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setCompany($company);
            $companyRepository->save($company, true);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('company/new.html.twig', [
            'form' => $form
        ]);
    }
}