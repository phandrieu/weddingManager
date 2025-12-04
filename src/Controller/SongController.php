<?php

namespace App\Controller;

use App\Entity\Song;
use App\Form\SongFormType;
use App\Repository\SongRepository;
use App\Repository\SongTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

#[Route('/songs')]
class SongController extends AbstractController
{
    #[Route('/', name: 'app_song_index')]
    public function index(Request $request, SongRepository $repo, SongTypeRepository $songTypeRepo): Response
    {
        // Filtres
        $search = trim((string) $request->query->get('q', ''));
        $typeFilter = trim((string) $request->query->get('type', ''));
        $kindFilter = $request->query->get('kind', 'all'); // all, chants, textes
        
        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = 12;

        // Construction de la requête
        $qb = $repo->createQueryBuilder('s')
            ->leftJoin('s.types', 't')
            ->addSelect('t')
            ->andWhere('s.suggestion = false')
            ->orderBy('s.name', 'ASC');

        // Filtre par type de cérémonie
        if ($kindFilter === 'chants') {
            $qb->andWhere('s.song = true');
        } elseif ($kindFilter === 'textes') {
            $qb->andWhere('s.song = false');
        }
        // Filtre par type de chant
        if ($typeFilter !== '') {
            $qb->andWhere('t.name = :typeName')
               ->setParameter('typeName', $typeFilter);
        }

        // Recherche
        if ($search !== '') {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(s.name)', ':search'),
                $qb->expr()->like('LOWER(s.lyrics)', ':search'),
                $qb->expr()->like('LOWER(s.lyricsAuthorName)', ':search'),
                $qb->expr()->like('LOWER(s.musicAuthorName)', ':search'),
                $qb->expr()->like('LOWER(s.interpretName)', ':search')
            ))->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        // Compte total
        $countQb = clone $qb;
        $paginatorForCount = new Paginator($countQb, true);
        $total = count($paginatorForCount);
        $pages = max(1, (int) ceil($total / $perPage));
        
        if ($page > $pages) {
            $page = $pages;
        }

        // Résultats paginés
        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage);

        $songs = $qb->getQuery()->getResult();

        // Récupérer toutes les suggestions pour les admin/musiciens
        $suggestions = $repo->createQueryBuilder('s')
            ->where('s.suggestion = true')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        // Récupérer tous les types pour le filtre
        $allTypes = [];
        $allSongs = $repo->findAll();
        foreach ($allSongs as $song) {
            foreach ($song->getTypes() as $type) {
                if (!in_array($type->getName(), $allTypes)) {
                    $allTypes[] = $type->getName();
                }
            }
        }
        sort($allTypes);

        $includeMesseTypes = true;
        $songTypes = $songTypeRepo->findOrderedByCelebrationPeriod($includeMesseTypes);

        return $this->render('song/index.html.twig', [
            'songs' => $songs,
            'suggestions' => $suggestions,
            'allTypes' => $allTypes,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'kindFilter' => $kindFilter,
            'songTypes' => $songTypes,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
            ],
        ]);
    }

    #[Route('/view/{id}', name: 'app_song_view')]
    public function view(Song $song): Response
    {
        return $this->render('song/view.html.twig', [
            'song' => $song,
        ]);
    }

    #[Route('/preview/{id}', name: 'app_song_preview', methods: ['GET'])]
    public function preview(Song $song): Response
    {
        return $this->render('song/_preview_modal_content.html.twig', [
            'song' => $song,
        ]);
    }

    #[Route('/approve/{id}', name: 'app_song_approve_suggestion', methods: ['POST'])]
    public function approveSong(Song $song, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('approve_song_' . $song->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $song->setSuggestion(false);
        $em->persist($song);
        $em->flush();

        $this->addFlash('success', 'Suggestion validée !');
        return $this->redirectToRoute('app_song_index');
    }
    #[Route('/delete/{id}', name: 'app_song_delete', methods: ['POST'])]
    public function delete(Request $request, Song $song, SongRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $song->getId(), $request->request->get('_token'))) {
            $repo->remove($song, true);
            $this->addFlash('success', 'Chant supprimé avec succès.');
        }

        return $this->redirectToRoute('app_song_index');
    }

    #[Route('/edit/{id?0}', name: 'app_song_edit')]
    public function edit(Request $request, Song $song = null, SongRepository $repo): Response
    {
        // déterminer si création ou édition
        $isNew = false;
        if (!$song) {
            $song = new Song();
            $isNew = true;
        }

        $form = $this->createForm(SongFormType::class, $song);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // récupérer l'utilisateur courant et mettre à jour les relations
            $user = $this->getUser();
            if ($user instanceof \App\Entity\User) {
                if ($isNew) {
                    // à la création : addedBy + lastEditBy
                    $song->setAddedBy($user);
                    $song->setAddedAt(new \DateTime());
                    $song->setLastEditAt(new \DateTime());
                    $song->setLastEditBy($user);
                } else {
                    // à la modification : lastEditBy uniquement
                    $song->setLastEditBy($user);
                    $song->setLastEditAt(new \DateTime());
                }
            }

            $repo->save($song, true);
            $this->addFlash('success', 'chant sauvegardée avec succès.');
            return $this->redirectToRoute('app_song_index');
        }

        return $this->render('song/edit.html.twig', [
            'form' => $form->createView(),
            'song' => $song,
        ]);
    }

    #[Route('/suggestion/create', name: 'song_suggestion_create', methods: ['POST'])]
    public function createSuggestion(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $name = trim((string) $request->request->get('name', ''));
        if (empty($name)) {
            return $this->json(['success' => false, 'message' => 'Le titre est obligatoire'], 400);
        }

        $isSong = $request->request->get('isSong', '1') === '1';

        $song = new Song();
        $song->setName($name);
        $song->setSuggestion(true);
        $song->setSong($isSong); // Chant (true) ou Lecture/Prière (false)
        $song->setAddedBy($user);
        $song->setAddedAt(new \DateTime());
        $song->setLastEditBy($user);
        $song->setLastEditAt(new \DateTime());

        // Champs optionnels
        if ($lyricsAuthor = trim((string) $request->request->get('lyricsAuthorName', ''))) {
            $song->setLyricsAuthorName($lyricsAuthor);
        }
        if ($musicAuthor = trim((string) $request->request->get('musicAuthorName', ''))) {
            $song->setMusicAuthorName($musicAuthor);
        }
        if ($interpret = trim((string) $request->request->get('interpretName', ''))) {
            $song->setInterpretName($interpret);
        }
        if ($editor = trim((string) $request->request->get('editorName', ''))) {
            $song->setEditorName($editor);
        }
        if ($textRef = trim((string) $request->request->get('textRef', ''))) {
            $song->setTextRef($textRef);
        }
        if ($textTranslation = trim((string) $request->request->get('textTranslationName', ''))) {
            $song->setTextTranslationName($textTranslation);
        }
        if ($previewUrl = trim((string) $request->request->get('previewUrl', ''))) {
            $song->setPreviewUrl($previewUrl);
        }
        if ($lyrics = trim((string) $request->request->get('lyrics', ''))) {
            $song->setLyrics($lyrics);
        }

        // Associer les types de chant si fournis
        $typeIds = $request->request->all('typeIds');
        if (is_array($typeIds) && !empty($typeIds)) {
            foreach ($typeIds as $typeId) {
                $songType = $em->getRepository(\App\Entity\SongType::class)->find($typeId);
                if ($songType) {
                    $song->addType($songType);
                }
            }
        }

        try {
            $em->persist($song);
            $em->flush();

            return $this->json([
                'success' => true,
                'songId' => $song->getId(),
                'message' => 'Suggestion créée avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }
}