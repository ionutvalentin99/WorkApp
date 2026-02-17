<?php

namespace App\Repository;

use App\Entity\Work;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Work>
 *
 * @method Work|null find($id, $lockMode = null, $lockVersion = null)
 * @method Work|null findOneBy(array $criteria, array $orderBy = null)
 * @method Work[]    findAll()
 * @method Work[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Work::class);
    }

    public function getActivePontaje($userId, $company): array
    {
        $date = new DateTime();
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $userId)
            ->andWhere('p.company = :company')
            ->setParameter('company', $company)
            ->andWhere('p.time_end >= :time_end')
            ->setParameter('time_end', $date)
            ->orderBy('p.time_start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getLastInsertByUser($userId)
    {
        return $this->createQueryBuilder('p')
            ->select('p.time_end')
            ->where('p.user = :user')
            ->setParameter('user', $userId)
            ->addOrderBy('p.time_end', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function getCompanyRecords($company, $user = null, $from = null, $to = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->andWhere('p.company = :company')
            ->setParameter('company', $company)
            ->orderBy('p.date', 'DESC')
            ->addOrderBy('p.time_end', 'DESC');

        if ($user !== null) {
            $qb->andWhere('p.user = :user')
                ->setParameter('user', $user);
        }

        if ($from !== null && $to === null) {
            $qb->andWhere('p.date = :from')
                ->setParameter('from', $from);
        }

        if ($from !== null && $to !== null) {
            $qb->andWhere('p.date BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->getQuery()->getResult();
    }

    public function getLastWorkRecords($userId)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('p.date', 'DESC')
            ->addOrderBy('p.time_end', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function save(Work $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Work $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
