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
    public function getLastInsertByUser(int $userId): array
    {
        $date = new DateTime();
        return $this->createQueryBuilder('p')
            ->select('p.date')
            ->addSelect('p.time_end')
            ->where('p.user = :user')
            ->setParameter('user', $userId)
            ->andWhere('p.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('p.date', Criteria::ASC)
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
