<?php

namespace App\Controller;

use App\Repository\InvitationRepository;
use App\Service\InvitationWorkflow;
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
    InvitationWorkflow $invitationWorkflow
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $session = $request->getSession();
        $user = $this->getUser();

        if ($user) {
            $token = $session->get('invitation_token');
            if ($token) {
                $invitation = $invitationRepo->findOneBy(['token' => $token, 'used' => false]);

                if ($invitation) {
                    if ($invitationWorkflow->requiresPayment($invitation)) {
                        $session->set('pending_invitation_token', $token);
                        $this->addFlash('info', 'Veuillez finaliser le paiement pour rejoindre ce mariage.');

                        return $this->redirectToRoute(
                            'app_wedding_invitation_checkout',
                            ['id' => $invitation->getWedding()->getId()]
                        );
                    }

                    $invitationWorkflow->attachUser($user, $invitation);
                    $session->remove('invitation_token');
                    $session->remove('pending_invitation_token');

                    $this->addFlash('success', 'Invitation acceptée.');

                    return $this->redirectToRoute(
                        'app_wedding_view',
                        ['id' => $invitation->getWedding()->getId()]
                    );
                }

                $session->remove('invitation_token');
                $this->addFlash('danger', 'Invitation invalide ou expirée.');
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by Symfony firewall.');
    }
}