<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Entity\Invitation;
use App\Entity\User;
use App\Form\WeddingFormType;
use App\Repository\WeddingRepository;
use App\Repository\InvitationRepository;
use App\Repository\SongRepository;
use App\Repository\SongTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/invitation')]
class InvitationController extends AbstractController
{
    #[Route('/accept/{token}', name: 'app_invitation_accept')]
    public function accept(string $token, InvitationRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
        $invitation = $repo->findOneBy(['token' => $token, 'used' => false]);

        if (!$invitation) {
            $this->addFlash('danger', 'Invitation invalide ou déjà utilisée.');
            return $this->redirectToRoute('app_home');
        }

        // Si déjà connecté → rattacher directement l’utilisateur
        if ($this->getUser()) {
            $user = $this->getUser();

            $this->attachUserToWedding($user, $invitation->getWedding(), $invitation->getRole(), $em);

            $invitation->setUsed(true);
            $em->flush();

            $this->addFlash('success', 'Vous avez rejoint le mariage.');
            return $this->redirectToRoute('app_wedding_view', ['id' => $invitation->getWedding()->getId()]);
        }

        // Sinon, rediriger vers login/register AVEC token dans l’URL
        $request->getSession()->set('invitation_token', $token);

        return $this->redirectToRoute('app_login', ['invitation' => 1]);
    }

    private function attachUserToWedding(User $user, Wedding $wedding, ?string $role, EntityManagerInterface $em): void
    {
        if ($role === 'musicien') {
            $wedding->addMusician($user);
            $user->addRole('ROLE_MUSICIAN');
        } elseif ($role === 'marie') {
            $wedding->setMarie($user);
        } elseif ($role === 'mariee') {
            $wedding->setMariee($user);
        }

        $em->persist($wedding);
        $em->persist($user);
    }
}