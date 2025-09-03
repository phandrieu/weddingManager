<?php

namespace App\Controller;

use App\Entity\SongType;
use App\Form\SongTypeType;
use App\Repository\SongTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/songtype')]
class SongTypeController extends AbstractController
{
    #[Route('/', name: 'app_songtype_index')]
    public function index(SongTypeRepository $repo): Response
    {
        $songTypes = $repo->findAll();
        return $this->render('songtype/index.html.twig', [
            'songTypes' => $songTypes
        ]);
    }
    #[Route('/delete/{id}', name: 'app_songtype_delete', methods: ['POST'])]
public function delete(Request $request, SongType $type, SongTypeRepository $repo): Response
{
    if ($this->isCsrfTokenValid('delete'.$type->getId(), $request->request->get('_token'))) {
        $repo->remove($type, true);
        $this->addFlash('success', 'Type de chant supprimé avec succès.');
    }

    return $this->redirectToRoute('app_songtype_index');
}

    #[Route('/view/{id}', name: 'app_songtype_view')]
    public function view(SongType $songType): Response
    {
        return $this->render('songtype/view.html.twig', [
            'songType' => $songType
        ]);
    }

    #[Route('/edit/{id?0}', name: 'app_songtype_edit')]
    public function edit(Request $request, SongType $songType = null, SongTypeRepository $repo): Response
    {
        if (!$songType) {
            $songType = new SongType();
        }

        $form = $this->createForm(SongTypeType::class, $songType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($songType, true);
            return $this->redirectToRoute('app_songtype_index');
        }

        return $this->render('songtype/edit.html.twig', [
            'form' => $form->createView(),
            'songType' => $songType,
        ]);
    }
}