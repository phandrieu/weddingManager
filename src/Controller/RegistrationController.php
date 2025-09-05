<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $user = new User();
            $user->setName($data['name']);
            $user->setFirstName($data['firstName']);
            $user->setRoles(['ROLE_USER']);

            if ($data['status'] === 'musicien') {
                $user->setRoles(['ROLE_USER', 'ROLE_MUSICIAN']);
                $user->setSubscription(isset($data['subscription']) && $data['subscription'] === '1');
            } else {
                $user->setRoles(['ROLE_USER', 'ROLE_MARIE']);
                $user->setSubscription(false);
            }

            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setAddressLine1($data['addressLine1']);
            $user->setAddressLine2($data['addressLine2']);
            $user->setAddressPostalCodeAndCity($data['addressPostalCodeAndCity']);

            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            // Si abonnement choisi → redirection vers Stripe
            if ($user->getRoles() && in_array('ROLE_MUSICIAN', $user->getRoles()) && $user->isSubscription()) {
                $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

                $session = $stripe->checkout->sessions->create([
                    'mode' => 'subscription',
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => ['name' => 'Abonnement musicien'],
                            'recurring' => ['interval' => 'month'],
                            'unit_amount' => 2000, // 20 € / mois
                        ],
                        'quantity' => 1,
                    ]],
                    'success_url' => $this->generateUrl('app_register_success', [], 0),
                    'cancel_url' => $this->generateUrl('app_register', [], 0),
                ]);

                return $this->redirect($session->url);
            }

            return $this->redirectToRoute('app_register_success');
        }

        return $this->render('registration/register.html.twig');
    }

    #[Route('/register/success', name: 'app_register_success')]
    public function success(): Response
    {
        return $this->render('registration/success.html.twig');
    }
}