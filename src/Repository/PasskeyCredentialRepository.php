<?php

namespace App\Repository;

use App\Entity\PasskeyCredential;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;

/**
 * @extends ServiceEntityRepository<PasskeyCredential>
 */
class PasskeyCredentialRepository extends ServiceEntityRepository implements PublicKeyCredentialSourceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasskeyCredential::class);
    }

    public function save(PasskeyCredential $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasskeyCredential $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find a credential by its ID (raw binary)
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        // PostgreSQL BYTEA comparison - need to use the entity directly
        $credentials = $this->findAll();
        
        foreach ($credentials as $credential) {
            $storedId = $credential->getPublicKeyCredentialId();
            if ($storedId === $publicKeyCredentialId) {
                return $credential->toPublicKeyCredentialSource();
            }
        }
        
        return null;
    }

    /**
     * Find all credentials for a user entity (implements interface)
     * 
     * @return PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        $userHandle = $publicKeyCredentialUserEntity->id;
        
        $credentials = $this->createQueryBuilder('c')
            ->where('c.userHandle = :userHandle')
            ->setParameter('userHandle', $userHandle)
            ->getQuery()
            ->getResult();

        return array_map(fn(PasskeyCredential $c) => $c->toPublicKeyCredentialSource(), $credentials);
    }

    /**
     * Find all credentials for a user handle (string)
     * 
     * @return PublicKeyCredentialSource[]
     */
    public function findAllByUserHandle(string $userHandle): array
    {
        $credentials = $this->createQueryBuilder('c')
            ->where('c.userHandle = :userHandle')
            ->setParameter('userHandle', $userHandle)
            ->getQuery()
            ->getResult();

        return array_map(fn(PasskeyCredential $c) => $c->toPublicKeyCredentialSource(), $credentials);
    }

    /**
     * Save a credential source
     */
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        // Find existing credential
        $existing = $this->createQueryBuilder('c')
            ->where('c.publicKeyCredentialId = :id')
            ->setParameter('id', $publicKeyCredentialSource->publicKeyCredentialId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existing instanceof PasskeyCredential) {
            // Update the counter
            $existing->setCounter($publicKeyCredentialSource->counter);
            $existing->setLastUsedAt(new \DateTimeImmutable());
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all credentials for a user
     * 
     * @return PasskeyCredential[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find credential by ID and user for deletion
     */
    public function findOneByIdAndUser(int $id, User $user): ?PasskeyCredential
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->andWhere('c.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count credentials for a user
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
