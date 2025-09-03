<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Repository\SongRepository; // <-- ajouter en haut
use App\Form\WeddingFormType;
use App\Repository\WeddingRepository;
use App\Repository\SongTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    #[Route('/delete/{id}', name: 'app_wedding_delete', methods: ['POST'])]
public function delete(Request $request, Wedding $wedding, WeddingRepository $repo): Response
{
    if ($this->isCsrfTokenValid('delete'.$wedding->getId(), $request->request->get('_token'))) {
        $repo->remove($wedding, true);
        $this->addFlash('success', 'Mariage supprimé avec succès.');
    }

    return $this->redirectToRoute('app_wedding_index');
}

    #[Route('/view/{id}', name: 'app_wedding_view')]
    public function view(Wedding $wedding, SongTypeRepository $songTypeRepo): Response
    {
        $songTypes = $songTypeRepo->findAll();

        return $this->render('wedding/view.html.twig', [
            'wedding' => $wedding,
            'songTypes' => $songTypes,
        ]);
    }

    
#[Route('/edit/{id?0}', name: 'app_wedding_edit')]
public function edit(
    Request $request,
    Wedding $wedding = null,
    WeddingRepository $repo,
    SongTypeRepository $songTypeRepo,
    SongRepository $songRepo // <-- injecter ici
): Response
{
    if (!$wedding) {
        $wedding = new Wedding();
    }

    $form = $this->createForm(WeddingFormType::class, $wedding);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Supprimer toutes les chansons actuelles
        foreach ($wedding->getSongs() as $song) {
            $wedding->removeSong($song);
        }

        // Ajouter les chansons sélectionnées
        $songsData = $request->request->all('songs'); // récupère tout le tableau 'songs'
        foreach ($songsData as $songId) {
            if ($songId) {
                $song = $songRepo->find($songId);
                if ($song) {
                    $wedding->addSong($song);
                }
            }
        }

        $repo->save($wedding, true);
        $this->addFlash('success', 'Mariage sauvegardé avec succès.');
        return $this->redirectToRoute('app_wedding_index');
    }

    $songTypes = $songTypeRepo->findAll();

    return $this->render('wedding/edit.html.twig', [
        'form' => $form->createView(),
        'wedding' => $wedding,
        'songTypes' => $songTypes,
    ]);
}
}