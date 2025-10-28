<?php

namespace App\Repository;

use App\Entity\WeddingSongSelection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeddingSongSelection>
 */
class WeddingSongSelectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeddingSongSelection::class);
    }
}
