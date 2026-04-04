<?php

namespace App\Twig;

use App\Entity\Company;
use App\Service\ActiveCompanyService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActiveCompanyExtension extends AbstractExtension
{
    public function __construct(private readonly ActiveCompanyService $activeCompanyService) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('active_company', $this->getActiveCompany(...)),
        ];
    }

    public function getActiveCompany(): ?Company
    {
        return $this->activeCompanyService->getActiveCompany();
    }
}
