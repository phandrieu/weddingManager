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

    /**
     * Recherche les mariages ayant au moins un participant correspondant aux e-mails fournis.
     *
     * @param string[] $emails
     * @return Wedding[]
     */
    public function findPotentialDuplicatesByEmails(array $emails): array
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static function ($email) {
                if ($email === null) {
                    return null;
                }

                $value = mb_strtolower(trim((string) $email));

                return $value !== '' ? $value : null;
            },
            $emails
        ))));

        if (empty($normalized)) {
            return [];
        }

        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.marie', 'marie')
            ->leftJoin('w.mariee', 'mariee')
            ->leftJoin('w.musicians', 'musician')
            ->leftJoin('w.parishUsers', 'parishUser')
            ->addSelect('marie', 'mariee', 'musician', 'parishUser')
            ->distinct();

        $expr = $qb->expr()->orX();

        foreach ($normalized as $index => $email) {
            $param = 'email' . $index;
            $expr->add($qb->expr()->eq('LOWER(marie.email)', ':' . $param));
            $expr->add($qb->expr()->eq('LOWER(mariee.email)', ':' . $param));
            $expr->add($qb->expr()->eq('LOWER(musician.email)', ':' . $param));
            $expr->add($qb->expr()->eq('LOWER(parishUser.email)', ':' . $param));
            $qb->setParameter($param, $email);
        }

        $qb->andWhere($expr)
            ->orderBy('w.date', 'DESC');

        return $qb->getQuery()->getResult();
    }
}