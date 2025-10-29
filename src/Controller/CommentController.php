<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Wedding;
use App\Entity\SongType;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comments')]
class CommentController extends AbstractController
{
    #[Route('/{weddingId}/{songTypeId}', name: 'app_comment_conversation', requirements: ['weddingId' => '\d+', 'songTypeId' => '\d+'])]
    public function conversation(
        string $weddingId,
        string $songTypeId,
        CommentRepository $commentRepo,
        EntityManagerInterface $em,
        NotificationService $notificationService,
        Request $request
    ): Response {
        // cast et validation defensive : s'assurer d'avoir des entiers valides
        $weddingIdInt = is_numeric($weddingId) ? (int)$weddingId : null;
        $songTypeIdInt = is_numeric($songTypeId) ? (int)$songTypeId : null;

        if (!$weddingIdInt || !$songTypeIdInt) {
            throw $this->createNotFoundException('Identifiants de mariage ou de type invalides.');
        }

        $wedding = $em->getRepository(Wedding::class)->find($weddingIdInt);
        $songType = $em->getRepository(SongType::class)->find($songTypeIdInt);

        if (!$wedding || !$songType) {
            throw $this->createNotFoundException('Mariage ou type de chant introuvable.');
        }

        $comment = new Comment();
        $comment->setWedding($wedding);
        $comment->setSongType($songType);
        $comment->setUser($this->getUser());
        $comment->setCreatedAt(new \DateTime());
        $comment->setUpdatedAt(new \DateTime());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            // Créer des notifications pour tous les participants du mariage
            $notificationService->createCommentNotifications($comment);

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('app_comment_conversation', [
                'weddingId' => $weddingId,
                'songTypeId' => $songTypeId,
            ]);
        }

        $comments = $commentRepo->findBy(
            ['wedding' => $wedding, 'songType' => $songType],
            ['createdAt' => 'ASC']
        );

        return $this->render('comment/commentConversation.html.twig', [
            'wedding' => $wedding,
            'songType' => $songType,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_comment_edit')]
    public function edit(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres commentaires.');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Commentaire mis à jour.');
            return $this->redirectToRoute('app_comment_conversation', [
                'weddingId' => $comment->getWedding()->getId(),
                'songTypeId' => $comment->getSongType()->getId(),
            ]);
        }

        return $this->render('comment/commentConversation.html.twig', [
            'editMode' => true,
            'commentToEdit' => $comment,
            'form' => $form->createView(),
            'comments' => [],
            'wedding' => $comment->getWedding(),
            'songType' => $comment->getSongType(),
        ]);
    }
}