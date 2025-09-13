<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\SongRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/repertoire')]
class RepertoireController extends AbstractController
{
    #[Route('/', name: 'app_repertoire_index')]
    public function index(SongRepository $songRepo): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $allSongs = $songRepo->findAll();
        $mySongs = $user->getRepertoire();

        return $this->render('repertoire/index.html.twig', [
            'allSongs' => $allSongs,
            'mySongs' => $mySongs,
        ]);
    }

    #[Route('/update', name: 'app_repertoire_update', methods: ['POST'])]
    public function update(Request $request, SongRepository $songRepo, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $songIds = $request->request->all('songs');
        $user->getRepertoire()->clear();

        foreach ($songIds as $id) {
            $song = $songRepo->find($id);
            if ($song) {
                $user->addSongToRepertoire($song);
            }
        }

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Répertoire mis à jour avec succès !');
        return $this->redirectToRoute('app_repertoire_index');
    }
}