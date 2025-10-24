<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\SongRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\InvitationWorkflow;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SongRepository $songRepository,
        InvitationRepository $invitationRepo,
        InvitationWorkflow $invitationWorkflow
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
            } elseif ($data['status'] === 'paroisse') {
                $user->setRoles(['ROLE_USER', 'ROLE_PARISH']);
            } else {
                $user->setRoles(['ROLE_USER']);
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

            // ⚡ Ajout du répertoire par défaut si musicien
            if (in_array('ROLE_MUSICIAN', $user->getRoles())) {
                $allSongs = $songRepository->findAll();
                foreach ($allSongs as $song) {
                    $user->addSongToRepertoire($song);
                }
            }

            // ⚡ Vérifier si une invitation est en attente
            $token = $request->getSession()->get('invitation_token');
            if ($token) {
                $invitation = $invitationRepo->findOneBy(['token' => $token, 'used' => false]);
                if ($invitation) {
                    if ($invitationWorkflow->requiresPayment($invitation)) {
                        $request->getSession()->set('pending_invitation_token', $token);
                        $this->addFlash('info', 'Veuillez finaliser le paiement pour rejoindre ce mariage.');

                        return $this->redirectToRoute(
                            'app_wedding_invitation_checkout',
                            ['id' => $invitation->getWedding()->getId()]
                        );
                    }

                    $invitationWorkflow->attachUser($user, $invitation);
                    $request->getSession()->remove('invitation_token');
                }
            }

            // ⚡ Si abonnement choisi → redirection vers Stripe
            if (in_array('ROLE_MUSICIAN', $user->getRoles()) && $user->isSubscription()) {
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