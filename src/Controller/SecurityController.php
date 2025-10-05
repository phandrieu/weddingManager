<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        InvitationRepository $invitationRepo,
        EntityManagerInterface $em,

    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($this->getUser()) {
    
    return $this->redirectToRoute('home');
}

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    private function attachUserToWedding($user, Invitation $invitation, EntityManagerInterface $em): void
    {
        $wedding = $invitation->getWedding();
        $role = $invitation->getRole();

        if ($role === 'musicien') {
            $wedding->addMusician($user);
            $user->addRole('ROLE_MUSICIAN');
        } elseif ($role === 'marie') {
            $wedding->setMarie($user);
            $user->addRole('ROLE_USER');
        } elseif ($role === 'mariee') {
            $wedding->setMariee($user);
            $user->addRole('ROLE_USER');
        }
        elseif ($role === 'mariee') {
            $wedding->setParish($user);
            $user->addRole('ROLE_USER');
            $user->addRole('ROLE_PARISH');
        }

        $invitation->setUsed(true);

        $em->persist($wedding);
        $em->persist($user);
        $em->persist($invitation);
        $em->flush();
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by Symfony firewall.');
    }
}