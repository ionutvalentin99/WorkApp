<?php

namespace App\Repository;

use App\Entity\Concedii;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Concedii>
 *
 * @method Concedii|null find($id, $lockMode = null, $lockVersion = null)
 * @method Concedii|null findOneBy(array $criteria, array $orderBy = null)
 * @method Concedii[]    findAll()
 * @method Concedii[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConcediiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Concedii::class);
    }

    public function save(Concedii $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Concedii $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllHolidaysDesc($user)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.user =:user')
            ->setParameter('user', $user)
            ->orderBy('c.start_date', Criteria::DESC)
            ->getQuery()
            ->getResult();
    }

    public function getPendingHolidaysAsc()
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.status =:status')
            ->setParameter('status', 'pending')
            ->orderBy('c.created', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Concedii[] Returns an array of Concedii objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Concedii
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
