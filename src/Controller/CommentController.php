<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\SongType;
use App\Entity\Wedding;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comments')]
class CommentController extends BaseController
{
    #[Route('/{wedding}/{songType}', name: 'app_comment_conversation', requirements: ['wedding' => '\\d+', 'songType' => '\\d+'])]
    public function conversation(
        Wedding $wedding,
        SongType $songType,
        CommentRepository $commentRepo,
        EntityManagerInterface $em,
        NotificationService $notificationService,
        Request $request
    ): Response {
        $comment = new Comment();
        $comment->setWedding($wedding);
        $comment->setSongType($songType);
        $comment->setUser($this->requireUser());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            // Créer des notifications pour tous les participants du mariage
            $notificationService->createCommentNotifications($comment);

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('app_comment_conversation', [
                'wedding' => $wedding->getId(),
                'songType' => $songType->getId(),
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
        $currentUser = $this->requireUser();

        if ($comment->getUser() !== $currentUser) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres commentaires.');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Commentaire mis à jour.');
            return $this->redirectToRoute('app_comment_conversation', [
                'wedding' => $comment->getWedding()->getId(),
                'songType' => $comment->getSongType()->getId(),
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