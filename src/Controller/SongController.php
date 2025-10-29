<?php

namespace App\Controller;

use App\Entity\Song;
use App\Form\SongFormType;
use App\Repository\SongRepository;
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
    public function index(Request $request, SongRepository $repo): Response
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

        return $this->render('song/index.html.twig', [
            'songs' => $songs,
            'suggestions' => $suggestions,
            'allTypes' => $allTypes,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'kindFilter' => $kindFilter,
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
    #[Route('/song/approve/{id}', name: 'app_song_approve_suggestion', methods: ['POST'])]
#[Route('/song/{id}/approve', name: 'app_song_approve_suggestion', methods: ['POST'])]
#[Route('/song/{id}/approve', name: 'app_song_approve_suggestion', methods: ['POST'])]
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
    if ($this->isCsrfTokenValid('delete'.$song->getId(), $request->request->get('_token'))) {
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
}