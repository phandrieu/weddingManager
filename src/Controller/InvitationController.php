<?php

namespace App\Controller;

use App\Repository\InvitationRepository;
use App\Service\InvitationWorkflow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/invitation')]
class InvitationController extends AbstractController
{
    #[Route('/accept/{token}', name: 'app_invitation_accept')]
    public function accept(
        string $token,
        InvitationRepository $repo,
        Request $request,
        InvitationWorkflow $invitationWorkflow
    ): Response {
        $invitation = $repo->findOneBy(['token' => $token, 'used' => false]);

        if (!$invitation) {
            $this->addFlash('danger', 'Invitation invalide ou déjà utilisée.');

            return $this->redirectToRoute('home');
        }

        $session = $request->getSession();
        $session->set('invitation_token', $token);

        if ($this->getUser()) {
            if ($invitationWorkflow->requiresPayment($invitation)) {
                $session->set('pending_invitation_token', $token);
                $this->addFlash('info', 'Veuillez finaliser le paiement pour rejoindre ce mariage.');

                return $this->redirectToRoute(
                    'app_wedding_invitation_checkout',
                    ['id' => $invitation->getWedding()->getId()]
                );
            }

            $invitationWorkflow->attachUser($this->getUser(), $invitation);
            $session->remove('invitation_token');
            $session->remove('pending_invitation_token');

            $this->addFlash('success', 'Vous avez rejoint le mariage.');

            return $this->redirectToRoute(
                'app_wedding_view',
                ['id' => $invitation->getWedding()->getId()]
            );
        }

        return $this->redirectToRoute('app_login', ['invitation' => 1]);
    }
}