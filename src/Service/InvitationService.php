<?php

namespace App\Service;

use App\Entity\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InvitationService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Attache un utilisateur à un mariage en fonction du rôle de l'invitation
     * et assigne les rôles appropriés à l'utilisateur
     */
    public function attachUserToWedding(User $user, Invitation $invitation): void
    {
        $wedding = $invitation->getWedding();
        $role = $invitation->getRole();

        switch ($role) {
            case 'musicien':
                if (!$wedding->getMusicians()->contains($user)) {
                    $wedding->addMusician($user);
                }
                if (!$user->hasRole('ROLE_MUSICIAN')) {
                    $user->addRole('ROLE_MUSICIAN');
                }
                break;

            case 'marie':
                if ($wedding->getMarie() !== $user) {
                    $wedding->setMarie($user);
                }
                break;

            case 'mariee':
                if ($wedding->getMariee() !== $user) {
                    $wedding->setMariee($user);
                }
                break;

            case 'paroisse':
                if (!$wedding->getParishUsers()->contains($user)) {
                    $wedding->addParishUser($user);
                }
                if (!$user->hasRole('ROLE_PARISH')) {
                    $user->addRole('ROLE_PARISH');
                }
                break;

            default:
                throw new \InvalidArgumentException("Rôle d'invitation invalide : {$role}");
        }

        // Marquer l'invitation comme utilisée
        $invitation->setUsed(true);

        // Persister les changements
        $this->em->persist($wedding);
        $this->em->persist($user);
        $this->em->persist($invitation);
        $this->em->flush();
    }

    /**
     * Vérifie si une invitation est valide
     */
    public function isInvitationValid(Invitation $invitation): bool
    {
        return !$invitation->isUsed();
    }
}
