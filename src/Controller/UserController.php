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

    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $repo->remove($user, true);
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
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
            if ($plainPassword) {
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