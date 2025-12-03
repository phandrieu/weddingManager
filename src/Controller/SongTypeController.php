<?php

namespace App\Controller;

use App\Entity\SongType;
use App\Form\SongTypeType;
use App\Repository\SongTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/songtype')]
class SongTypeController extends AbstractController
{
    #[Route('/', name: 'app_songtype_index')]
    public function index(SongTypeRepository $repo): Response
    {
        $songTypes = $repo->findBy([], ['ordre' => 'ASC', 'name' => 'ASC']);
        return $this->render('songType/index.html.twig', [
            'songTypes' => $songTypes
        ]);
    }

    #[Route('/reorder', name: 'app_songtype_reorder', methods: ['POST'])]
    public function reorder(Request $request, SongTypeRepository $repo): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload) || !isset($payload['order']) || !is_array($payload['order'])) {
            return new JsonResponse(['success' => false, 'message' => 'Requête invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $csrfToken = $payload['csrf'] ?? '';
        if (!$this->isCsrfTokenValid('songtype_reorder', $csrfToken)) {
            return new JsonResponse(['success' => false, 'message' => 'Jeton CSRF invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $orderedIds = array_values(array_unique(array_map('intval', $payload['order'])));

        $totalTypes = $repo->count([]);
        if (count($orderedIds) !== $totalTypes) {
            return new JsonResponse(['success' => false, 'message' => 'Liste des types incomplète.'], Response::HTTP_BAD_REQUEST);
        }

        $position = 1;
        foreach ($orderedIds as $typeId) {
            $songType = $repo->find($typeId);
            if (!$songType) {
                return new JsonResponse(['success' => false, 'message' => 'Type de chant introuvable.'], Response::HTTP_BAD_REQUEST);
            }
            $songType->setOrdre($position++);
        }

        $repo->getEntityManager()->flush();

        return new JsonResponse(['success' => true]);
    }
    #[Route('/delete/{id}', name: 'app_songtype_delete', methods: ['POST'])]
    public function delete(Request $request, SongType $type, SongTypeRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $type->getId(), $request->request->get('_token'))) {
            $repo->remove($type, true);
            $this->addFlash('success', 'Type de chant supprimé avec succès.');
        }

        return $this->redirectToRoute('app_songtype_index');
    }

    #[Route('/view/{id}', name: 'app_songtype_view')]
    public function view(SongType $songType): Response
    {
        return $this->render('songType/view.html.twig', [
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

        return $this->render('songType/edit.html.twig', [
            'form' => $form->createView(),
            'songType' => $songType,
        ]);
    }
}