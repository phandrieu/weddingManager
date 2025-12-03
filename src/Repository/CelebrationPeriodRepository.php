<?php

namespace App\Repository;

use App\Entity\CelebrationPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CelebrationPeriod>
 */
class CelebrationPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CelebrationPeriod::class);
    }

    public function save(CelebrationPeriod $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);

        if ($flush) {
            $em->flush();
        }
    }

    public function remove(CelebrationPeriod $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);

        if ($flush) {
            $this->_em->flush();
        }
    }

    //    /**
    //     * @return CelebrationPeriod[] Returns an array of CelebrationPeriod objects
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

    //    public function findOneBySomeField($value): ?CelebrationPeriod
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
