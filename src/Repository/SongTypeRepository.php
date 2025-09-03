<?php

namespace App\Repository;

use App\Entity\SongType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SongType>
 */
class SongTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SongType::class);
    }
    public function save(SongType $entity, bool $flush = false): void
{
    $em = $this->getEntityManager();
    $em->persist($entity);
    if ($flush) {
        $em->flush();
    }
}

public function remove(SongType $entity, bool $flush = false): void
{
    $this->_em->remove($entity);
    if ($flush) {
        $this->_em->flush();
    }
}

//    /**
//     * @return SongType[] Returns an array of SongType objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SongType
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
