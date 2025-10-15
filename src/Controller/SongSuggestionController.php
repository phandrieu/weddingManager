<?php
namespace App\Controller;

use App\Entity\Song;
use App\Form\SongSuggestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SongSuggestionController extends AbstractController
{
    #[Route('/suggest-song', name: 'app_song_suggest')]
    public function suggest(Request $request, EntityManagerInterface $em): Response
    {
        $song = new Song();
        $song->setSuggestion(true); // marquer comme suggestion
        // Plus besoin de setType() car maintenant une song a plusieurs types (relation ManyToMany)
        // Les types seront définis via le formulaire

        $form = $this->createForm(SongSuggestionType::class, $song);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($song);
            $em->flush();

            $this->addFlash('success', 'Votre suggestion a été enregistrée !');
            return $this->redirectToRoute('app_song_index'); // ou une page de confirmation
        }

        return $this->render('song/suggest.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}