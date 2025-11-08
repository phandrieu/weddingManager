<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('/users')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index')]
    public function index(UserRepository $repo): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $repo->findAll(),
        ]);
    }

    #[Route('/view/{id}', name: 'app_user_show')]
    public function view(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, User $user, UserRepository $repo, EntityManagerInterface $em): Response
    {
        // Si la requête est GET, rediriger avec un message d'erreur
        if ($request->isMethod('GET')) {
            $this->addFlash('warning', 'La suppression d\'utilisateur doit être effectuée via le formulaire approprié.');
            return $this->redirectToRoute('app_user_index');
        }

        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_user_index');
        }

        // Vérifier les relations qui pourraient empêcher la suppression
        $hasRelations = false;
        $relationMessages = [];

        if (!$user->getWeddings()->isEmpty()) {
            $relationMessages[] = sprintf('%d mariage(s) comme marié', $user->getWeddings()->count());
            $hasRelations = true;
        }

        if (!$user->getWeddingsAsMariee()->isEmpty()) {
            $relationMessages[] = sprintf('%d mariage(s) comme mariée', $user->getWeddingsAsMariee()->count());
            $hasRelations = true;
        }

        if (!$user->getWeddingsAsMusicians()->isEmpty()) {
            $relationMessages[] = sprintf('%d mariage(s) comme musicien', $user->getWeddingsAsMusicians()->count());
            $hasRelations = true;
        }

        if (!$user->getWeddingsAsParish()->isEmpty()) {
            $relationMessages[] = sprintf('%d mariage(s) comme utilisateur paroisse', $user->getWeddingsAsParish()->count());
            $hasRelations = true;
        }

        if (!$user->getComments()->isEmpty()) {
            $relationMessages[] = sprintf('%d commentaire(s)', $user->getComments()->count());
            $hasRelations = true;
        }

        if ($hasRelations) {
            $this->addFlash('error', sprintf(
                'Impossible de supprimer cet utilisateur car il est lié à : %s. Veuillez d\'abord supprimer ou modifier ces éléments.',
                implode(', ', $relationMessages)
            ));
            return $this->redirectToRoute('app_user_index');
        }

        try {
            $repo->remove($user, true);
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_user_index');
    }

    // Cette route gère à la fois création et édition
    #[Route('/edit/{id?0}', name: 'app_user_edit')]
public function edit(Request $request, UserPasswordHasherInterface $hasher, User $user = null, UserRepository $repo): Response
{
    $isNew = !$user;
    if ($isNew) {
        $user = new User();
    }

    $form = $this->createForm(UserType::class, $user, ['is_new' => $isNew]);
    $form->handleRequest($request);
    $user->setSubscription(false);
    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        } elseif ($isNew) {
            throw new \Exception('Le mot de passe est obligatoire pour un nouvel utilisateur.');
        }

        $repo->save($user, true);
        $this->addFlash('success', 'Utilisateur sauvegardé avec succès.');
        return $this->redirectToRoute('app_user_index');
    }

    return $this->render('user/edit.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
}

    #[Route('/profile', name: 'app_user_profile')]
    public function profile(Request $request, UserPasswordHasherInterface $hasher, UserRepository $repo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $currentPassword = $form->get('currentPassword')->getData();
            
            if ($plainPassword) {
                // Vérifier que l'ancien mot de passe a été fourni
                if (!$currentPassword) {
                    $this->addFlash('error', 'Vous devez entrer votre mot de passe actuel pour le modifier.');
                    return $this->redirectToRoute('app_user_profile');
                }
                
                // Vérifier que l'ancien mot de passe est correct
                if (!$hasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                    return $this->redirectToRoute('app_user_profile');
                }
                
                // Hasher et définir le nouveau mot de passe
                $hashedPassword = $hasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $repo->save($user, true);
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}