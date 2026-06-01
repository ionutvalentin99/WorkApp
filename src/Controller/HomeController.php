<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\WorkRepository;
use App\Service\ActiveCompanyService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly WorkRepository $pontajeRepository,
        private readonly ActiveCompanyService $activeCompanyService
    )
    {
    }

    private function buildChartDatasets($user, $company): array
    {
        $now = new DateTime();

        // --- Săptămâna curentă ---
        $weekStart = (clone $now)->modify('monday this week 00:00:00');
        $weekEnd   = (clone $now)->modify('sunday this week 23:59:59');
        $dayNames  = ['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm', 'Dum'];
        $weekHours = array_fill(0, 7, 0.0);
        foreach ($this->pontajeRepository->getRecordsForPeriod($user, $company, $weekStart, $weekEnd) as $record) {
            if ($record->getTimeEnd() === null) { continue; }
            $index = (int)$record->getDate()->format('N') - 1;
            $day   = $record->getTimeEnd()->diff($record->getTimeStart());
            $weekHours[$index] = round($weekHours[$index] + $day->h + ($day->i / 60), 2);
        }

        // --- Luna curentă ---
        $monthStart  = (clone $now)->modify('first day of this month 00:00:00');
        $monthEnd    = (clone $now)->modify('last day of this month 23:59:59');
        $daysInMonth = (int)$monthEnd->format('d');
        $monthHours  = array_fill(1, $daysInMonth, 0.0);
        foreach ($this->pontajeRepository->getRecordsForPeriod($user, $company, $monthStart, $monthEnd) as $r) {
            if ($r->getTimeEnd() === null) { continue; }
            $d = $r->getTimeEnd()->diff($r->getTimeStart());
            $monthHours[(int)$r->getDate()->format('j')] = round(
                $monthHours[(int)$r->getDate()->format('j')] + $d->h + ($d->i / 60), 2
            );
        }

        // --- Helper: grupare pe luni ---
        $buildMonthly = function (int $months) use ($now, $user, $company): array {
            $start   = (clone $now)->modify("first day of -" . ($months - 1) . " month 00:00:00");
            $end     = (clone $now)->modify('last day of this month 23:59:59');
            $buckets = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $key           = (clone $now)->modify("first day of -$i month")->format('Y-m');
                $buckets[$key] = 0.0;
            }
            foreach ($this->pontajeRepository->getRecordsForPeriod($user, $company, $start, $end) as $r) {
                if ($r->getTimeEnd() === null) { continue; }
                $key = $r->getDate()->format('Y-m');
                if (isset($buckets[$key])) {
                    $d             = $r->getTimeEnd()->diff($r->getTimeStart());
                    $buckets[$key] = round($buckets[$key] + $d->h + ($d->i / 60), 2);
                }
            }
            $labels = array_map(/**
             * @throws Exception
             */ fn($k) => (new DateTime($k))->format('M Y'), array_keys($buckets));
            return ['labels' => $labels, 'data' => array_values($buckets)];
        };

        $m3 = $buildMonthly(3);
        $m6 = $buildMonthly(6);

        return [
            'week'        => ['labels' => $dayNames,              'data' => array_values($weekHours)],
            'month'       => ['labels' => range(1, $daysInMonth), 'data' => array_values($monthHours)],
            'threeMonths' => ['labels' => $m3['labels'],           'data' => $m3['data']],
            'sixMonths'   => ['labels' => $m6['labels'],           'data' => $m6['data']],
        ];
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isVerified = $user?->isVerified();
        $lastUserWorkRecords = null;
        $activeCompany = $this->activeCompanyService->getActiveCompany();
        $openPontaj = null;
        $chartDatasets = [];

        $weekTotals  = ['hours' => 0.0, 'count' => 0, 'label' => ''];
        $monthTotals = ['hours' => 0.0, 'count' => 0, 'label' => ''];

        if ($user && $activeCompany) {
            $now        = new DateTime();
            $weekStart  = (clone $now)->modify('monday this week 00:00:00');
            $weekEnd    = (clone $now)->modify('sunday this week 23:59:59');
            $monthStart = (clone $now)->modify('first day of this month 00:00:00');
            $monthEnd   = (clone $now)->modify('last day of this month 23:59:59');

            $monthNames = ['Ianuarie','Februarie','Martie','Aprilie','Mai','Iunie','Iulie','August','Septembrie','Octombrie','Noiembrie','Decembrie'];

            $weekTotals['label']  = $weekStart->format('d.m') . ' – ' . $weekEnd->format('d.m');
            $monthTotals['label'] = $monthNames[(int)$now->format('n') - 1] . ' ' . $now->format('Y');

            foreach ($this->pontajeRepository->getRecordsForPeriod($user, $activeCompany, $weekStart, $weekEnd) as $r) {
                if ($r->getTimeEnd() === null) { continue; }
                $d = $r->getTimeEnd()->diff($r->getTimeStart());
                $weekTotals['hours'] += $d->h + ($d->i / 60);
                $weekTotals['count']++;
            }
            $weekTotals['hours'] = round($weekTotals['hours'], 1);

            foreach ($this->pontajeRepository->getRecordsForPeriod($user, $activeCompany, $monthStart, $monthEnd) as $r) {
                if ($r->getTimeEnd() === null) { continue; }
                $d = $r->getTimeEnd()->diff($r->getTimeStart());
                $monthTotals['hours'] += $d->h + ($d->i / 60);
                $monthTotals['count']++;
            }
            $monthTotals['hours'] = round($monthTotals['hours'], 1);

            $lastUserWorkRecords = $this->pontajeRepository->getLastWorkRecords($user->getId(), $activeCompany->getId());
            $openPontaj          = $this->pontajeRepository->getOpenPontaj($user, $activeCompany);
            $chartDatasets       = $this->buildChartDatasets($user, $activeCompany);
        }

        return $this->render('home/index.html.twig', [
            'user'         => $user,
            'isVerified'   => $isVerified,
            'pontaje'      => $lastUserWorkRecords,
            'openPontaj'   => $openPontaj,
            'weekTotals'   => $weekTotals,
            'monthTotals'  => $monthTotals,
            'date'         => (new DateTime())->format('Y-m-d'),
            'chartDatasets'=> json_encode($chartDatasets),
        ]);
    }
}
