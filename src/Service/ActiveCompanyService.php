<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\User;
use App\Repository\CompanyRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ActiveCompanyService
{
    private const SESSION_KEY = 'active_company_id';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CompanyRepository $companyRepository,
        private readonly Security $security,
    ) {}

    public function getActiveCompany(): ?Company
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user) {
            return null;
        }

        $session = $this->requestStack->getSession();
        $storedId = $session->get(self::SESSION_KEY);

        if ($storedId) {
            $company = $this->companyRepository->find($storedId);
            if ($company && $user->getCompanies()->contains($company)) {
                return $company;
            }
        }

        // Auto-select first available company
        if (!$user->getCompanies()->isEmpty()) {
            $company = $user->getCompanies()->first();
            $session->set(self::SESSION_KEY, $company->getId());
            return $company;
        }

        return null;
    }

    public function setActiveCompany(Company $company): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $company->getId());
    }

    public function clearActiveCompany(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }
}
