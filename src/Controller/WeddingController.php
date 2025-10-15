<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Entity\Invitation;
use App\Form\WeddingFormType;
use App\Repository\WeddingRepository;
use App\Repository\SongRepository;
use App\Repository\SongTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use DateTime;


#[Route('/mariages')]
class WeddingController extends AbstractController
{
    #[Route('/', name: 'app_wedding_index')]
    public function index(WeddingRepository $repo): Response
    {
        return $this->render('wedding/index.html.twig', [
            'weddings' => $repo->findAll(),
        ]);
    }

    #[Route('/view/{id}', name: 'app_wedding_view')]
    #[Route('/view/{id}', name: 'app_wedding_view')]
public function view(Wedding $wedding, SongTypeRepository $songTypeRepo): Response
{
    $songTypes = $songTypeRepo->findAll();

    $songs = [];
    foreach ($wedding->getMusicians() as $musician) {
        foreach ($musician->getRepertoire() as $song) {
            $songs[$song->getId()] = $song;
        }
    }

    return $this->render('wedding/view.html.twig', [
        'wedding' => $wedding,
        'songTypes' => $songTypes,
        'availableSongs' => $songs,
    ]);
}

#[Route('/edit/{id?0}', name: 'app_wedding_edit')]
public function edit(
    Request $request,
    WeddingRepository $repo,
    SongTypeRepository $songTypeRepo,
    SongRepository $songRepo,
    Wedding $wedding = null
): Response {
    if (!$wedding) {
        $wedding = new Wedding();
    }

    $form = $this->createForm(WeddingFormType::class, $wedding);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Nettoyage des songs existantes
        foreach ($wedding->getSongs() as $song) {
            $wedding->removeSong($song);
        }

        $songsData = $request->request->all('songs'); // IDs des songs sélectionnées
        foreach ($songsData as $songId) {
            if ($songId) {
                $song = $songRepo->find($songId);
                if ($song) {
                    $wedding->addSong($song);
                }
            }
        }

        // Paiement Stripe pour non-admins et mariage non encore créé
        if (!$this->isGranted('ROLE_ADMIN') && !$wedding->getId()) {
            $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Création de mariage'],
                        'unit_amount' => 5000,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl(
                    'app_wedding_payment_success',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'cancel_url' => $this->generateUrl(
                    'app_wedding_edit',
                    ['id' => $wedding->getId() ?: 0],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]);

            $request->getSession()->set('wedding_data', $wedding);

            return $this->redirect($session->url);
        }

        $repo->save($wedding, true);
        $this->addFlash('success', 'Mariage sauvegardé avec succès.');
        return $this->redirectToRoute('app_wedding_index');
    }

    $songTypes = $songTypeRepo->findAll();

    // 🔑 Filtrer les chants disponibles = union des répertoires des musiciens rattachés
    $availableSongs = [];
    foreach ($wedding->getMusicians() as $musician) {
        foreach ($musician->getRepertoire() as $song) {
            $availableSongs[$song->getId()] = $song;
        }
    }

    // 🔑 Construire la liste des chants disponibles, regroupés par type
    $availableSongsByType = [];

    foreach ($songTypes as $songType) {
        $songsForType = [];

        if (count($wedding->getMusicians()) > 0) {
            foreach ($wedding->getMusicians() as $musician) {
                foreach ($musician->getRepertoire() as $song) {
                    if ($song->getTypes()->contains($songType)) {
                        $songsForType[$song->getId()] = $song;
                    }
                }
            }
        }

        if (empty($songsForType)) {
            foreach ($songType->getSongs() as $song) {
                $songsForType[$song->getId()] = $song;
            }
        }

        $availableSongsByType[$songType->getId()] = array_values($songsForType);
    }

    return $this->render('wedding/edit.html.twig', [
        'form' => $form->createView(),
        'wedding' => $wedding,
        'songTypes' => $songTypes,
        'availableSongs' => $availableSongs,
        'availableSongsByType' => $availableSongsByType,

        'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
    ]);
}

    #[Route('/delete/{id}', name: 'app_wedding_delete', methods: ['POST'])]
    public function delete(Request $request, Wedding $wedding, WeddingRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wedding->getId(), $request->request->get('_token'))) {
            $repo->remove($wedding, true);
            $this->addFlash('success', 'Mariage supprimé avec succès.');
        }

        return $this->redirectToRoute('app_wedding_index');
    }

    #[Route('/payment/success', name: 'app_wedding_payment_success')]
    public function paymentSuccess(Request $request, WeddingRepository $repo): Response
    {
        $weddingSession = $request->getSession()->get('wedding_data');

        if ($weddingSession) {
            $em = $repo->getEntityManager();

            // Si le wedding existe déjà en base, on le recharge
            if ($weddingSession->getId()) {
                $wedding = $repo->find($weddingSession->getId());
            } else {
                $wedding = $weddingSession;
            }

            // Attacher les Users existants pour que Doctrine les gère correctement
            if ($wedding->getMarie()) {
                $wedding->setMarie($em->getReference(\App\Entity\User::class, $wedding->getMarie()->getId()));
            }

            if ($wedding->getMariee()) {
                $wedding->setMariee($em->getReference(\App\Entity\User::class, $wedding->getMariee()->getId()));
            }

            foreach ($wedding->getMusicians() as $i => $musician) {
                $wedding->getMusicians()->set(
                    $i,
                    $em->getReference(\App\Entity\User::class, $musician->getId())
                );
            }

            $repo->save($wedding, true);
            $request->getSession()->remove('wedding_data');
            $this->addFlash('success', 'Paiement réussi et mariage créé !');
        }

        return $this->redirectToRoute('app_wedding_index');
    }
   #[Route('/{id}/invite', name: 'app_wedding_invite')]
public function invite(
    Wedding $wedding,
    Request $request,
    EntityManagerInterface $em,
    MailerInterface $mailer,
    SongTypeRepository $songTypeRepo,
    SongRepository $songRepo
): Response {
    $generatedInvitationLink = null;

    if ($request->isMethod('POST')) {
        $email = $request->request->get('email');
        $role = $request->request->get('role');

        $invitation = new Invitation();
        $invitation->setEmail($email);
        $invitation->setWedding($wedding);
        $invitation->setRole($role);
        $invitation->setToken(bin2hex(random_bytes(32)));
        $invitation->setUsed(false);
        $invitation->setCreatedAt(new \DateTimeImmutable());
        $em->persist($invitation);
        $em->flush();

        // Génération du lien
        $generatedInvitationLink = $this->generateUrl(
            'app_invitation_accept',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Envoi de l'email
        $emailMessage = (new Email())
            ->from('noreply@monsite.com')
            ->to($email)
            ->subject('Invitation à rejoindre un mariage')
            ->html("Vous avez été invité à rejoindre un mariage.<br>
                    <a href='$generatedInvitationLink'>Cliquez ici pour accepter l’invitation</a>");

        $mailer->send($emailMessage);

        $this->addFlash('success', 'Invitation envoyée !');

        // 🔑 Reprend les mêmes données que edit()
        $songTypes = $songTypeRepo->findAll();

        $availableSongs = [];
        foreach ($wedding->getMusicians() as $musician) {
            foreach ($musician->getRepertoire() as $song) {
                $availableSongs[$song->getId()] = $song;
            }
        }

        $availableSongsByType = [];
        foreach ($songTypes as $songType) {
            $songsForType = [];

            if (count($wedding->getMusicians()) > 0) {
                foreach ($wedding->getMusicians() as $musician) {
                    foreach ($musician->getRepertoire() as $song) {
                        if ($song->getTypes()->contains($songType)) {
                            $songsForType[$song->getId()] = $song;
                        }
                    }
                }
            }

            if (empty($songsForType)) {
                foreach ($songType->getSongs() as $song) {
                    $songsForType[$song->getId()] = $song;
                }
            }

            $availableSongsByType[$songType->getId()] = array_values($songsForType);
        }

        return $this->render('wedding/edit.html.twig', [
            'form' => $this->createForm(WeddingFormType::class, $wedding)->createView(),
            'wedding' => $wedding,
            'songTypes' => $songTypes,
            'availableSongs' => $availableSongs,
            'availableSongsByType' => $availableSongsByType,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'generatedInvitationLink' => $generatedInvitationLink,
        ]);
    }

    return $this->redirectToRoute('app_wedding_edit', ['id' => $wedding->getId()]);
}
    #[Route('/checkout', name: 'app_wedding_create_checkout', methods: ['POST'])]
    public function createCheckout(Request $request): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Admins n’ont pas besoin de payer'], 403);
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Création mariage'],
                        'unit_amount' => 5000,
                    ],
                    'quantity' => 1,
                ]
            ],
            'success_url' => $this->generateUrl(
                'app_wedding_edit',
                ['id' => 0],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?paid=1',
            'cancel_url' => $this->generateUrl(
                'app_wedding_edit',
                ['id' => 0],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->json(['sessionId' => $session->id]);
    }
    #[Route('/{id}/archive', name: 'app_wedding_archive', methods: ['POST'])]
    public function archive(Request $request, Wedding $wedding, WeddingRepository $repo): Response
    {
        if (!$this->isCsrfTokenValid('archive' . $wedding->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_wedding_index');
        }

        $wedding->setArchive(true);
        $repo->save($wedding, true);

        $this->addFlash('success', 'Mariage archivé.');
        return $this->redirectToRoute('app_wedding_index');
    }

    #[Route('/{id}/unarchive', name: 'app_wedding_unarchive', methods: ['POST'])]
    public function unarchive(Request $request, Wedding $wedding, WeddingRepository $repo): Response
    {
        if (!$this->isCsrfTokenValid('unarchive' . $wedding->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_wedding_index');
        }

        $wedding->setArchive(false);
        $repo->save($wedding, true);

        $this->addFlash('success', 'Mariage désarchivé.');
        return $this->redirectToRoute('app_wedding_index');
    }
}