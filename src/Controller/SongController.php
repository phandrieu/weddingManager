<?php

namespace App\Controller;

use App\Entity\Song;
use App\Form\SongFormType;
use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/songs')]
class SongController extends AbstractController
{
    #[Route('/', name: 'app_song_index')]
    public function index(SongRepository $repo): Response
    {
        return $this->render('song/index.html.twig', [
            'songs' => $repo->findAll(),
        ]);
    }

    #[Route('/view/{id}', name: 'app_song_view')]
    public function view(Song $song): Response
    {
        return $this->render('song/view.html.twig', [
            'song' => $song,
        ]);
    }
    #[Route('/delete/{id}', name: 'app_song_delete', methods: ['POST'])]
public function delete(Request $request, Song $song, SongRepository $repo): Response
{
    if ($this->isCsrfTokenValid('delete'.$song->getId(), $request->request->get('_token'))) {
        $repo->remove($song, true);
        $this->addFlash('success', 'Chant supprimé avec succès.');
    }

    return $this->redirectToRoute('app_song_index');
}

    #[Route('/edit/{id?0}', name: 'app_song_edit')]
    public function edit(Request $request, Song $song = null, SongRepository $repo): Response
    {
        if (!$song) {
            $song = new Song();
        }

        $form = $this->createForm(SongFormType::class, $song);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($song, true);
            $this->addFlash('success', 'chant sauvegardée avec succès.');
            return $this->redirectToRoute('app_song_index');
        }

        return $this->render('song/edit.html.twig', [
            'form' => $form->createView(),
            'song' => $song,
        ]);
    }
}