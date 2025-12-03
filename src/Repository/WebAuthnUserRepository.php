<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * WebAuthn User Repository adapter
 */
class WebAuthnUserRepository implements PublicKeyCredentialUserEntityRepositoryInterface
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = $this->userRepository->findOneBy(['email' => $username]);

        if (!$user) {
            return null;
        }

        return $this->createWebAuthnUser($user);
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        // Decode the base64 user handle to get the user ID
        $userId = (int) base64_decode($userHandle);
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return null;
        }

        return $this->createWebAuthnUser($user);
    }

    /**
     * Generate user handle from User entity (must be bytes for WebAuthn)
     */
    public function generateUserHandle(User $user): string
    {
        // User handle should be opaque bytes - we use base64 encoded user ID
        return base64_encode((string) $user->getId());
    }

    /**
     * Create WebAuthn user entity from User
     */
    public function createWebAuthnUser(User $user): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            $this->generateUserHandle($user),
            $user->getFullName() ?: $user->getEmail()
        );
    }

    /**
     * Find User entity by user handle
     */
    public function findUserByHandle(string $userHandle): ?User
    {
        $userId = (int) base64_decode($userHandle);
        return $this->userRepository->find($userId);
    }
}
