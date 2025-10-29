<?php

namespace App\Service;

use App\Entity\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InvitationWorkflow
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function requiresPayment(Invitation $invitation): bool
    {
        $role = $invitation->getRole();
        if (!in_array($role, ['marie', 'mariee'], true)) {
            return false;
        }

        $wedding = $invitation->getWedding();
        if (!$wedding) {
            return false;
        }

        // Le paiement est requis si :
        // 1. Le mariage nécessite le paiement du couple (requiresCouplePayment = true)
        // 2. ET le mariage n'est pas encore payé (isPaid = false)
        return $wedding->isRequiresCouplePayment() && !$wedding->isPaid();
    }

    public function attachUser(User $user, Invitation $invitation): void
    {
        $wedding = $invitation->getWedding();
        if (!$wedding) {
            return;
        }

        switch ($invitation->getRole()) {
            case 'musicien':
                $wedding->addMusician($user);
                $user->addRole('ROLE_MUSICIAN');
                $user->addRole('ROLE_USER');
                break;
            case 'marie':
                $wedding->setMarie($user);
                $user->addRole('ROLE_USER');
                break;
            case 'mariee':
                $wedding->setMariee($user);
                $user->addRole('ROLE_USER');
                break;
            case 'paroisse':
                $wedding->addParishUser($user);
                $user->addRole('ROLE_PARISH');
                $user->addRole('ROLE_USER');
                break;
        }

        $invitation->setUsed(true);

        $this->em->persist($wedding);
        $this->em->persist($user);
        $this->em->persist($invitation);
        $this->em->flush();
    }
}
