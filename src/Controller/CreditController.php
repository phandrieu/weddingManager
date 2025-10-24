<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/credits')]
class CreditController extends AbstractController
{
    #[Route('/buy', name: 'app_credits_buy')]
    public function buy(Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Seulement les ROLE_PARISH et ROLE_MUSICIAN peuvent acheter des crédits
        if (!$this->isGranted('ROLE_PARISH') && !$this->isGranted('ROLE_MUSICIAN')) {
            $this->addFlash('danger', 'Seuls les musiciens et paroisses peuvent acheter des crédits.');
            return $this->redirectToRoute('home');
        }

        if ($request->isMethod('POST')) {
            $quantity = (int) $request->request->get('quantity', 1);
            
            if ($quantity < 1 || $quantity > 100) {
                $this->addFlash('danger', 'Quantité invalide (entre 1 et 100).');
                return $this->redirectToRoute('app_credits_buy');
            }

            $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Crédit(s) pour création de mariage'],
                        'unit_amount' => 1000, // 10€ par crédit
                    ],
                    'quantity' => $quantity,
                ]],
                'success_url' => $this->generateUrl('app_credits_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('app_credits_buy', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => [
                    'user_id' => $user->getId(),
                    'credits' => $quantity,
                ],
            ]);

            return $this->redirect($session->url);
        }

        return $this->render('credit/buy.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/success', name: 'app_credits_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->query->get('session_id');
        
        if (!$sessionId) {
            $this->addFlash('danger', 'Session invalide.');
            return $this->redirectToRoute('home');
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            
            if ($session->payment_status === 'paid') {
                $user = $this->getUser();
                $credits = (int) $session->metadata->credits;
                
                $user->addCredits($credits);
                $em->flush();
                
                $this->addFlash('success', "Vous avez acheté {$credits} crédit(s) avec succès ! Vous avez maintenant {$user->getCredits()} crédit(s).");
            } else {
                $this->addFlash('warning', 'Le paiement n\'est pas encore confirmé.');
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la vérification du paiement.');
        }

        return $this->redirectToRoute('home');
    }
}
