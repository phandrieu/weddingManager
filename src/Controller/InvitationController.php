<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Entity\Invitation;
use App\Entity\User;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/invitation')]
class InvitationController extends AbstractController
{
    #[Route('/accept/{token}', name: 'app_invitation_accept')]
public function accept(string $token, InvitationRepository $repo, Request $request, EntityManagerInterface $em): Response
{
    $invitation = $repo->findOneBy(['token' => $token, 'used' => false]);

    if (!$invitation) {
        $this->addFlash('danger', 'Invitation invalide ou déjà utilisée.');
        return $this->redirectToRoute('home');
    }

    if ($this->getUser()) {
        $this->attachUserToWedding($this->getUser(), $invitation, $em);
        $this->addFlash('success', 'Vous avez rejoint le mariage.');
        return $this->redirectToRoute('app_wedding_view', ['id' => $invitation->getWedding()->getId()]);
    }

    $request->getSession()->set('invitation_token', $token);

    return $this->redirectToRoute('app_login', ['invitation' => 1]);
}

    private function attachUserToWedding(User $user, Invitation $invitation, EntityManagerInterface $em): void
    {
        $wedding = $invitation->getWedding();
        $role = $invitation->getRole();

        if ($role === 'musicien') {
            $wedding->addMusician($user);
            $user->addRole('ROLE_MUSICIAN');
            $user->addRole('ROLE_USER');
        } elseif ($role === 'marie') {
            $wedding->setMarie($user);
            $user->addRole('ROLE_USER');
        } elseif ($role === 'mariee') {
            $wedding->setMariee($user);
            $user->addRole('ROLE_USER');
        }

        $invitation->setUsed(true);

        $em->persist($wedding);
        $em->persist($user);
        $em->persist($invitation);
        $em->flush();
    }
}