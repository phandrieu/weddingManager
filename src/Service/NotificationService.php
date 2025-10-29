<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Invitation;
use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Wedding;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Crée une notification pour une invitation envoyée
     * Note : Cette méthode est appelée APRÈS l'acceptation de l'invitation
     * car les invitations par email ne créent pas de notifications jusqu'à
     * ce que l'utilisateur s'inscrive/se connecte
     */
    public function createInvitationNotification(Invitation $invitation, User $user): void
    {
        $notification = new Notification();
        $notification->setType('invitation');
        $notification->setUser($user);
        $notification->setInvitation($invitation);
        $notification->setWedding($invitation->getWedding());
        
        $wedding = $invitation->getWedding();
        $creator = $wedding->getCreatedBy();
        
        $inviterName = $creator->getFirstName() . ' ' . $creator->getName();
        $weddingName = $this->getWeddingDisplayName($wedding);
        
        $notification->setMessage(sprintf(
            '%s vous a invité(e) au mariage de %s',
            $inviterName,
            $weddingName
        ));
        
        // Lien vers la page du mariage
        $notification->setLink('/wedding/' . $wedding->getId());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
    
    /**
     * Retourne le nom d'affichage du mariage
     */
    private function getWeddingDisplayName(Wedding $wedding): string
    {
        $marieName = $wedding->getMarie() ? $wedding->getMarie()->getFirstName() : 'Marié';
        $marieeName = $wedding->getMariee() ? $wedding->getMariee()->getFirstName() : 'Mariée';
        
        return $marieName . ' & ' . $marieeName;
    }

    /**
     * Crée des notifications pour un nouveau commentaire
     * Notifie tous les participants du mariage (sauf l'auteur du commentaire)
     */
    public function createCommentNotifications(Comment $comment): void
    {
        try {
            $wedding = $comment->getWedding();
            $author = $comment->getUser();
            $songType = $comment->getSongType();

            // Récupérer tous les participants du mariage
            $participants = [];
            
            // Le créateur du mariage
            if ($wedding->getCreatedBy()) {
                $participants[] = $wedding->getCreatedBy();
            }
            
            // Les deux mariés (s'ils sont différents du créateur)
            if ($wedding->getMarie()) {
                $participants[] = $wedding->getMarie();
            }
            if ($wedding->getMariee()) {
                $participants[] = $wedding->getMariee();
            }
            
            // Les musiciens
            foreach ($wedding->getMusicians() as $musician) {
                $participants[] = $musician;
            }
            
            // Les utilisateurs de la paroisse
            foreach ($wedding->getParishUsers() as $parishUser) {
                $participants[] = $parishUser;
            }

            // Créer une notification pour chaque participant unique (sauf l'auteur)
            $uniqueParticipants = [];
            foreach ($participants as $participant) {
                if ($participant) {
                    $uniqueParticipants[$participant->getId()] = $participant;
                }
            }
            
            $notificationsCreated = 0;
            foreach ($uniqueParticipants as $participant) {
                if ($participant->getId() === $author->getId()) {
                    continue; // Ne pas notifier l'auteur du commentaire
                }

                $notification = new Notification();
                $notification->setType('comment');
                $notification->setUser($participant);
                $notification->setComment($comment);
                $notification->setWedding($wedding);
                
                $weddingName = $this->getWeddingDisplayName($wedding);
                
                $notification->setMessage(sprintf(
                    '%s %s a commenté "%s" dans le mariage de %s',
                    $author->getFirstName(),
                    $author->getName(),
                    $songType->getName(),
                    $weddingName
                ));
                
                // Lien vers le mariage avec le hash du type de chant pour ouvrir la modale
                $notification->setLink('/wedding/' . $wedding->getId() . '#songtype-' . $songType->getId());

                $this->entityManager->persist($notification);
                $notificationsCreated++;
            }

            if ($notificationsCreated > 0) {
                $this->entityManager->flush();
            }
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas l'ajout du commentaire
            error_log('Erreur lors de la création des notifications : ' . $e->getMessage());
        }
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $this->entityManager->flush();
        }
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(User $user): void
    {
        $repo = $this->entityManager->getRepository(Notification::class);
        $repo->markAllAsReadByUser($user->getId());
    }

    /**
     * Supprime une notification
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * Supprime les notifications lues de plus de 30 jours
     */
    public function cleanOldNotifications(): void
    {
        $repo = $this->entityManager->getRepository(Notification::class);
        $repo->deleteOldReadNotifications(30);
    }
}
