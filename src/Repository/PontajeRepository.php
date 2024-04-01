<?php

namespace App\Repository;

use App\Entity\Pontaje;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pontaje>
 *
 * @method Pontaje|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pontaje|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pontaje[]    findAll()
 * @method Pontaje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PontajeRepository extends ServiceEntityRepository
{
    public function getSingleDaySearchResult($user, $date): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.date = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
    public function getIntervalSearchLessThan($user, $from, $to): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.date >= :from')
            ->setParameter('from', $from)
            ->andWhere('p.date <= :to')
            ->setParameter('to', $to)
            ->orderBy('p.date', Criteria::DESC)
            ->addOrderBy('p.time_end', Criteria::DESC)
            ->getQuery()
            ->getResult();
    }
    public function getDefaultEntries($user): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.date', Criteria::DESC)
            ->addOrderBy('p.time_end', Criteria::DESC)
            ->getQuery()
            ->getResult();
    }
    public function getActivePontaje($userId): array
    {
        $date = new DateTime();
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $userId)
            ->andWhere('p.time_end >= :time_end')
            ->setParameter('time_end', $date)
            ->orderBy('p.time_start', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    public function getLastInsertByUser($userId)
    {
        return $this->createQueryBuilder('p')
            ->select('p.time_end')
            ->where('p.user = :user')
            ->setParameter('user', $userId)
            ->addOrderBy('p.time_end', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pontaje::class);
    }

    public function save(Pontaje $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pontaje $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Pontaje[] Returns an array of Pontaje objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Pontaje
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
