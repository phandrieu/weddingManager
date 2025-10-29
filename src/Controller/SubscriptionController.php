<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/subscription')]
class SubscriptionController extends AbstractController
{
    #[Route('/subscribe', name: 'app_subscription_subscribe')]
    public function subscribe(Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Seulement les ROLE_MUSICIAN peuvent souscrire à un abonnement
        if (!$this->isGranted('ROLE_MUSICIAN')) {
            $this->addFlash('danger', 'Seuls les musiciens peuvent souscrire à un abonnement.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Si déjà abonné
        if ($user->isSubscription()) {
            $this->addFlash('info', 'Vous êtes déjà abonné.');
            return $this->redirectToRoute('app_user_profile');
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

        try {
            $session = $stripe->checkout->sessions->create([
                'mode' => 'subscription',
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Abonnement Musicien - Notre Messe de Mariage'],
                        'recurring' => ['interval' => 'month'],
                        'unit_amount' => 2000, // 20 € / mois
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => $this->generateUrl('app_subscription_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('app_user_profile', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => [
                    'user_id' => $user->getId(),
                ],
            ]);

            return $this->redirect($session->url);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_user_profile');
        }
    }

    #[Route('/success', name: 'app_subscription_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->query->get('session_id');
        
        if (!$sessionId) {
            $this->addFlash('danger', 'Session invalide.');
            return $this->redirectToRoute('app_user_profile');
        }

        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            
            if ($session->payment_status === 'paid' || $session->status === 'complete') {
                $user->setSubscription(true);
                $em->flush();
                
                $this->addFlash('success', 'Votre abonnement a été activé avec succès ! Vous bénéficiez maintenant de tous les avantages de l\'abonnement musicien.');
            } else {
                $this->addFlash('warning', 'Le paiement n\'est pas encore confirmé.');
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la vérification du paiement : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_user_profile');
    }

    #[Route('/cancel', name: 'app_subscription_cancel', methods: ['POST'])]
    public function cancel(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_MUSICIAN')) {
            $this->addFlash('danger', 'Seuls les musiciens peuvent gérer leur abonnement.');
            return $this->redirectToRoute('app_user_profile');
        }

        if (!$user->isSubscription()) {
            $this->addFlash('info', 'Vous n\'avez pas d\'abonnement actif.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Vérification du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cancel_subscription', $token)) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_user_profile');
        }

        // Annuler l'abonnement
        $user->setSubscription(false);
        $em->flush();

        $this->addFlash('success', 'Votre abonnement a été annulé. Il restera actif jusqu\'à la fin de la période en cours.');

        return $this->redirectToRoute('app_user_profile');
    }
}
