<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notification')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService
    ) {}

    /**
     * Récupère toutes les notifications de l'utilisateur connecté
     */
    #[Route('/list', name: 'app_notification_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $repo = $this->entityManager->getRepository(Notification::class);
        
        $acceptsJson = str_contains($request->headers->get('Accept', ''), 'application/json');
        if (!$request->isXmlHttpRequest() && !$acceptsJson) {
            return $this->redirectToRoute('home');
        }

        $notifications = $repo->findAllByUser($user);
        $unreadCount = $repo->countUnreadByUser($user);

        $data = [
            'notifications' => array_map(function($notification) {
                return [
                    'id' => $notification->getId(),
                    'type' => $notification->getType(),
                    'message' => $notification->getMessage(),
                    'link' => $notification->getLink(),
                    'isRead' => $notification->isRead(),
                    'createdAt' => $notification->getCreatedAt()->format('d/m/Y H:i'),
                    'invitationId' => $notification->getInvitation()?->getId(),
                    'weddingId' => $notification->getWedding()?->getId(),
                ];
            }, $notifications),
            'unreadCount' => $unreadCount,
        ];

        return $this->json($data);
    }

    /**
     * Récupère le nombre de notifications non lues
     */
    #[Route('/count', name: 'app_notification_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $repo = $this->entityManager->getRepository(Notification::class);
        
        $count = $repo->countUnreadByUser($user);

        return $this->json(['count' => $count]);
    }

    /**
     * Marque une notification comme lue
     */
    #[Route('/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    public function markAsRead(Notification $notification): JsonResponse
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->markAsRead($notification);

        return $this->json(['success' => true]);
    }

    /**
     * Marque toutes les notifications comme lues
     */
    #[Route('/read-all', name: 'app_notification_read_all', methods: ['POST'])]
    public function markAllAsRead(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $this->notificationService->markAllAsRead($user);

        return $this->json(['success' => true]);
    }

    /**
     * Supprime une notification
     */
    #[Route('/{id}/delete', name: 'app_notification_delete', methods: ['DELETE'])]
    public function delete(Notification $notification): JsonResponse
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->deleteNotification($notification);

        return $this->json(['success' => true]);
    }

    /**
     * Accepte une invitation depuis une notification
     */
    #[Route('/{id}/accept-invitation', name: 'app_notification_accept_invitation', methods: ['POST'])]
    public function acceptInvitation(Notification $notification): JsonResponse
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que c'est bien une notification d'invitation
        if ($notification->getType() !== 'invitation') {
            return $this->json(['error' => 'Type de notification invalide'], Response::HTTP_BAD_REQUEST);
        }

        $invitation = $notification->getInvitation();
        
        if (!$invitation) {
            return $this->json(['error' => 'Invitation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Rediriger vers le workflow d'acceptation d'invitation existant
        return $this->json([
            'success' => true,
            'redirectUrl' => $this->generateUrl('app_invitation_accept', ['token' => $invitation->getToken()])
        ]);
    }

    /**
     * Refuse une invitation depuis une notification
     */
    #[Route('/{id}/reject-invitation', name: 'app_notification_reject_invitation', methods: ['POST'])]
    public function rejectInvitation(Notification $notification): JsonResponse
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que c'est bien une notification d'invitation
        if ($notification->getType() !== 'invitation') {
            return $this->json(['error' => 'Type de notification invalide'], Response::HTTP_BAD_REQUEST);
        }

        $invitation = $notification->getInvitation();
        
        if (!$invitation) {
            return $this->json(['error' => 'Invitation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Marquer l'invitation comme utilisée (refusée)
        // Note: Les invitations n'ont pas de status, on les marque juste comme "used"
        $invitation->setUsed(true);
        $this->entityManager->flush();

        // Supprimer la notification
        $this->notificationService->deleteNotification($notification);

        return $this->json([
            'success' => true,
            'message' => 'Invitation refusée'
        ]);
    }
}
