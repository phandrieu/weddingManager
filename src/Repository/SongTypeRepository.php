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

    /**
     * @return SongType[]
     */
    public function findOrderedByCelebrationPeriod(bool $includeMesseTypes = true): array
    {
        $qb = $this->createQueryBuilder('st')
            ->leftJoin('st.celebrationPeriod', 'cp')
            ->addSelect('cp')
            ->addSelect('COALESCE(cp.periodOrder, 999) AS HIDDEN cpSortOrder')
            ->addSelect('COALESCE(st.ordre, 999) AS HIDDEN stSortOrder')
            ->orderBy('cpSortOrder', 'ASC')
            ->addOrderBy('cp.fullName', 'ASC')
            ->addOrderBy('stSortOrder', 'ASC')
            ->addOrderBy('st.name', 'ASC');

        if (!$includeMesseTypes) {
            $qb->andWhere('st.messe = :isMesse')
               ->setParameter('isMesse', false);
        }

        return $qb->getQuery()->getResult();
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
