<?php

namespace App\Repository;

use App\Entity\Wedding;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wedding>
 *
 * @method Wedding|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wedding|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wedding[]    findAll()
 * @method Wedding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeddingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wedding::class);
    }

    /**
     * Sauvegarde un mariage
     */
    public function save(Wedding $wedding, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($wedding);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Supprime un mariage
     */
    public function remove(Wedding $wedding, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($wedding);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Exemple : trouver les mariages par date
     */
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.date = :date')
            ->setParameter('date', $date)
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple : trouver le dernier mariage
     */
    public function findLastWedding(): ?Wedding
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}