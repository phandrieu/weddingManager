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
        EntityManagerInterface $em
    ): Response {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // ⚡ Vérification après connexion réussie
        if ($this->getUser()) {
            $token = $request->getSession()->get('invitation_token');
            if ($token) {
                $invitation = $invitationRepo->findOneBy(['token' => $token, 'used' => false]);
                if ($invitation) {
                    $this->attachUserToWedding($this->getUser(), $invitation, $em);
                    $request->getSession()->remove('invitation_token');
                }
            }
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
        } elseif ($role === 'marie') {
            $wedding->setMarie($user);
        } elseif ($role === 'mariee') {
            $wedding->setMariee($user);
        }

        $invitation->setUsed(true);
        $em->persist($wedding);
        $em->persist($invitation);
        $em->flush();
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}