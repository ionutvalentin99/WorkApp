<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CompanyRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyRequest>
 *
 * @method CompanyRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyRequest[]    findAll()
 * @method CompanyRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyRequest::class);
    }

    public function add(CompanyRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompanyRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Verifică dacă un utilizator are deja o cerere în așteptare pentru o anumită companie.
     * Folosim asta pentru a ascunde butonul de "Alăturare" dacă a trimis deja cererea.
     */
    public function hasPendingRequest(User $user, Company $company): bool
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.company = :company')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('company', $company)
            ->setParameter('status', 'PENDING')
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }

    /**
     * Returnează toate cererile în așteptare pentru o companie.
     * Folosim asta în panoul Owner-ului pentru a aproba/respinge cereri.
     * * @return CompanyRequest[] Returns an array of CompanyRequest objects
     */
    public function findPendingRequestsForCompany(Company $company): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.company = :company')
            ->andWhere('c.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', 'PENDING')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}