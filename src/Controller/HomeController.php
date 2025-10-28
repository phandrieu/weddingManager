<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wedding;
use App\Repository\UserRepository;
use App\Repository\WeddingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(WeddingRepository $weddingRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('public');
        }

        $roles = $user->getRoles();
        $nonDefaultRoles = array_diff($roles, ['ROLE_USER']);

        $isAdmin = in_array('ROLE_ADMIN', $roles, true);
        $isMusician = in_array('ROLE_MUSICIAN', $roles, true);
        $isParish = in_array('ROLE_PARISH', $roles, true);
        $isCoupleOnly = empty($nonDefaultRoles);

        $dashboards = [
            'couple' => null,
            'musician' => null,
            'parish' => null,
            'admin' => null,
        ];

        $today = new \DateTimeImmutable('today');
        $allWeddings = null;

        if ($isCoupleOnly || $isAdmin) {
            $coupleWeddings = [];
            foreach ($user->getWeddings() as $wedding) {
                if ($wedding->getId()) {
                    $coupleWeddings[$wedding->getId()] = $wedding;
                }
            }
            foreach ($user->getWeddingsAsMariee() as $wedding) {
                if ($wedding->getId()) {
                    $coupleWeddings[$wedding->getId()] = $wedding;
                }
            }

            $coupleCards = [];
            foreach ($coupleWeddings as $wedding) {
                $fieldStatus = [
                    'Date du mariage' => $wedding->getDate() !== null,
                    'Heure' => $wedding->getTime() !== null,
                    'Paroisse' => (bool) $wedding->getParish(),
                    'Église' => (bool) $wedding->getChurch(),
                    'Adresse' => (bool) $wedding->getAddressLine1(),
                    'Contacts paroissiaux' => $wedding->getParishUsers()->count() > 0,
                    'Musiciens associés' => $wedding->getMusicians()->count() > 0,
                    'Déroulé des chants' => $wedding->getSongSelections()->count() > 0 || $wedding->getSongs()->count() > 0,
                    'Option de paiement' => $wedding->getPaymentOption() !== null,
                    'Type de célébration' => $wedding->isMesse() !== null,
                ];

                $completed = 0;
                $missing = [];
                foreach ($fieldStatus as $label => $isFilled) {
                    if ($isFilled) {
                        ++$completed;
                    } else {
                        $missing[] = $label;
                    }
                }

                $totalFields = count($fieldStatus);
                $progress = $totalFields > 0 ? (int) round(($completed / $totalFields) * 100) : 0;

                $coupleCards[] = [
                    'id' => $wedding->getId(),
                    'title' => $this->formatWeddingTitle($wedding),
                    'date' => $wedding->getDate()?->format('d/m/Y'),
                    'messe' => $wedding->isMesse(),
                    'progress' => $progress,
                    'missing' => $missing,
                    'viewUrl' => $this->generateUrl('app_wedding_view', ['id' => $wedding->getId()]),
                    'editUrl' => $this->generateUrl('app_wedding_edit', ['id' => $wedding->getId()]),
                    'isArchived' => (bool) $wedding->isArchive(),
                ];
            }

            usort($coupleCards, static function (array $a, array $b): int {
                return ($a['date'] ?? '') <=> ($b['date'] ?? '');
            });

            $dashboards['couple'] = [
                'weddings' => $coupleCards,
            ];
        }

        if ($isMusician || $isAdmin) {
            $musicianWeddings = [];
            if ($isMusician) {
                foreach ($user->getWeddingsAsMusicians() as $wedding) {
                    if ($wedding->getId()) {
                        $musicianWeddings[$wedding->getId()] = $wedding;
                    }
                }
            } else {
                $allWeddings ??= $weddingRepository->findAll();
                foreach ($allWeddings as $wedding) {
                    if ($wedding->getId()) {
                        $musicianWeddings[$wedding->getId()] = $wedding;
                    }
                }
            }

            $upcoming = array_filter($musicianWeddings, static function (Wedding $wedding) use ($today) {
                $date = $wedding->getDate();
                return $date instanceof \DateTimeInterface && $date >= $today;
            });

            usort($upcoming, static function (Wedding $a, Wedding $b): int {
                $dateA = $a->getDate();
                $dateB = $b->getDate();
                if ($dateA === null && $dateB === null) {
                    return 0;
                }
                if ($dateA === null) {
                    return 1;
                }
                if ($dateB === null) {
                    return -1;
                }

                return $dateA <=> $dateB;
            });

            $upcomingSummaries = array_map(function (Wedding $wedding) {
                $totalSelections = $wedding->getSongSelections()->count();
                $validatedByMusician = 0;
                foreach ($wedding->getSongSelections() as $selection) {
                    if ($selection->isValidatedByMusician()) {
                        ++$validatedByMusician;
                    }
                }

                return [
                    'id' => $wedding->getId(),
                    'title' => $this->formatWeddingTitle($wedding),
                    'date' => $wedding->getDate()?->format('d/m/Y'),
                    'time' => $wedding->getTime()?->format('H\hi'),
                    'church' => $wedding->getChurch() ?? 'À confirmer',
                    'pendingValidations' => max($totalSelections - $validatedByMusician, 0),
                    'totalSelections' => $totalSelections,
                    'viewUrl' => $this->generateUrl('app_wedding_view', ['id' => $wedding->getId()]),
                ];
            }, array_slice($upcoming, 0, 3));

            $totalAmount = 0.0;
            foreach ($musicianWeddings as $wedding) {
                $totalAmount += (float) ($wedding->getMontantTotal() ?? 0);
            }

            $dashboards['musician'] = [
                'upcoming' => $upcomingSummaries,
                'totalAmount' => $totalAmount,
                'weddingCount' => count($musicianWeddings),
                'credits' => $user->getCredits(),
            ];
        }

        if ($isParish || $isAdmin) {
            $parishWeddings = [];
            if ($isParish) {
                foreach ($user->getWeddingsAsParish() as $wedding) {
                    if ($wedding->getId()) {
                        $parishWeddings[$wedding->getId()] = $wedding;
                    }
                }
            } else {
                $allWeddings ??= $weddingRepository->findAll();
                foreach ($allWeddings as $wedding) {
                    if ($wedding->getId()) {
                        $parishWeddings[$wedding->getId()] = $wedding;
                    }
                }
            }

            $upcomingParish = array_filter($parishWeddings, static function (Wedding $wedding) use ($today) {
                $date = $wedding->getDate();
                return $date instanceof \DateTimeInterface && $date >= $today;
            });

            usort($upcomingParish, static function (Wedding $a, Wedding $b): int {
                $dateA = $a->getDate();
                $dateB = $b->getDate();
                if ($dateA === null && $dateB === null) {
                    return 0;
                }
                if ($dateA === null) {
                    return 1;
                }
                if ($dateB === null) {
                    return -1;
                }

                return $dateA <=> $dateB;
            });

            $upcomingParishSummaries = array_map(function (Wedding $wedding) {
                $totalSelections = $wedding->getSongSelections()->count();
                $validatedByParish = 0;
                foreach ($wedding->getSongSelections() as $selection) {
                    if ($selection->isValidatedByParish()) {
                        ++$validatedByParish;
                    }
                }

                return [
                    'id' => $wedding->getId(),
                    'title' => $this->formatWeddingTitle($wedding),
                    'date' => $wedding->getDate()?->format('d/m/Y'),
                    'time' => $wedding->getTime()?->format('H\hi'),
                    'church' => $wedding->getChurch() ?? 'À confirmer',
                    'pendingValidations' => max($totalSelections - $validatedByParish, 0),
                    'totalSelections' => $totalSelections,
                    'viewUrl' => $this->generateUrl('app_wedding_view', ['id' => $wedding->getId()]),
                ];
            }, array_slice($upcomingParish, 0, 3));

            $dashboards['parish'] = [
                'upcoming' => $upcomingParishSummaries,
                'weddingCount' => count($parishWeddings),
                'credits' => $user->getCredits(),
            ];
        }

        if ($isAdmin) {
            $upcomingGlobal = $weddingRepository->findBy([], ['date' => 'ASC'], 10);
            $upcomingGlobal = array_filter($upcomingGlobal, static function (Wedding $wedding) use ($today) {
                $date = $wedding->getDate();
                return $date instanceof \DateTimeInterface && $date >= $today;
            });

            $upcomingGlobal = array_slice($upcomingGlobal, 0, 5);

            $dashboards['admin'] = [
                'userCount' => $userRepository->count([]),
                'weddingCount' => $weddingRepository->count([]),
                'upcomingGlobal' => array_map(function (Wedding $wedding) {
                    return [
                        'id' => $wedding->getId(),
                        'title' => $this->formatWeddingTitle($wedding),
                        'date' => $wedding->getDate()?->format('d/m/Y'),
                        'viewUrl' => $this->generateUrl('app_wedding_view', ['id' => $wedding->getId()]),
                    ];
                }, $upcomingGlobal),
            ];
        }

        return $this->render('home/index.html.twig', [
            'dashboards' => $dashboards,
            'is_admin' => $isAdmin,
        ]);
    }

    private function formatWeddingTitle(Wedding $wedding): string
    {
        $groom = $this->formatParticipantName($wedding->getMarie(), 'Marié à inviter');
        $bride = $this->formatParticipantName($wedding->getMariee(), 'Mariée à inviter');

        if ($groom && $bride) {
            return sprintf('Mariage de %s & %s', $groom, $bride);
        }

        if ($groom) {
            return sprintf('Mariage de %s', $groom);
        }

        if ($bride) {
            return sprintf('Mariage de %s', $bride);
        }

        return $wedding->getChurch() ? sprintf('Mariage à %s', $wedding->getChurch()) : 'Mariage sans couple identifié';
    }

    private function formatParticipantName(?User $user, string $fallback): string
    {
        if (!$user) {
            return $fallback;
        }

        $full = trim(sprintf('%s %s', $user->getFirstName() ?? '', $user->getName() ?? ''));
        if ($full !== '') {
            return $full;
        }

        return $user->getEmail() ?? $fallback;
    }
    #[Route('/', name: 'public')]
    public function public(WeddingRepository $weddingRepository): Response
    {
        // Récupère tous les mariages
        $weddings = $weddingRepository->findAll();

        return $this->render('home/public.html.twig', [
            
        ]);
    }
}