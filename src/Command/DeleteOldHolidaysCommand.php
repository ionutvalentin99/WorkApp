<?php

namespace App\Command;

use App\Repository\ConcediiRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delete-old-holidays',
    description: 'Add a short description for your command',
)]
class DeleteOldHolidaysCommand extends Command
{
    public function __construct(
        private readonly ConcediiRepository     $holidayRepository,
        private readonly EntityManagerInterface $entityManager,
        string                                  $name = null
    )
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
            foreach ($this->holidayRepository->findAll() as $holiday) {
                $holidayEndDate = $holiday->getEndDate();
                $expiredDate = $holidayEndDate->modify('+1 month');

                if ($holiday->getStatus() !== 'pending' && $expiredDate <= new DateTime('now')) {
                    $this->holidayRepository->remove($holiday);
                    $this->entityManager->flush();
                }
            }

            return Command::SUCCESS;
        }
    }
