<?php

namespace App\Service\Dashboard;

use App\Entity\User;
use App\Entity\Wedding;
use App\Repository\UserRepository;
use App\Repository\WeddingRepository;
use App\ViewModel\DashboardContext;
use DateTimeImmutable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DashboardBuilder
{
    public function __construct(
        private readonly WeddingRepository $weddingRepository,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function build(User $user): DashboardContext
    {
        $roles = $user->getRoles();
        $nonDefaultRoles = array_diff($roles, ['ROLE_USER']);

        $flags = [
            'is_admin' => in_array('ROLE_ADMIN', $roles, true),
            'is_musician' => in_array('ROLE_MUSICIAN', $roles, true),
            'is_parish' => in_array('ROLE_PARISH', $roles, true),
            'is_couple_only' => empty($nonDefaultRoles),
        ];

        $dashboards = [
            'couple' => $this->buildCoupleDashboard($user, $flags),
            'musician' => $this->buildMusicianDashboard($user, $flags),
            'parish' => $this->buildParishDashboard($user, $flags),
            'admin' => $this->buildAdminDashboard($flags),
        ];

        return new DashboardContext($dashboards, $flags['is_admin']);
    }

    /**
     * @param array{is_admin: bool, is_couple_only: bool} $flags
     */
    private function buildCoupleDashboard(User $user, array $flags): ?array
    {
        if (!$flags['is_couple_only'] && !$flags['is_admin']) {
            return null;
        }

        $coupleWeddings = $this->indexWeddingsById(
            [...$user->getWeddings()->toArray(), ...$user->getWeddingsAsMariee()->toArray()]
        );

        $cards = [];
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
                'Type de célébration' => $wedding->isMesse(),
            ];

            $completed = array_sum(array_map(static fn (bool $filled): int => $filled ? 1 : 0, $fieldStatus));
            $missing = array_keys(array_filter($fieldStatus, static fn (bool $filled): bool => $filled === false));
            $totalFields = count($fieldStatus);
            $progress = $totalFields > 0 ? (int) round(($completed / $totalFields) * 100) : 0;

            $cards[] = [
                'id' => $wedding->getId(),
                'title' => $this->formatWeddingTitle($wedding),
                'date' => $wedding->getDate()?->format('d/m/Y'),
                'messe' => $wedding->isMesse(),
                'progress' => $progress,
                'missing' => $missing,
                'viewUrl' => $this->urlGenerator->generate('app_wedding_view', ['id' => $wedding->getId()]),
                'editUrl' => $this->urlGenerator->generate('app_wedding_edit', ['id' => $wedding->getId()]),
                'isArchived' => (bool) $wedding->isArchive(),
                'entity' => $wedding,
            ];
        }

        usort($cards, static function (array $a, array $b): int {
            return ($a['date'] ?? '') <=> ($b['date'] ?? '');
        });

        return ['weddings' => $cards];
    }

    /**
     * @param array{is_admin: bool, is_musician: bool} $flags
     */
    private function buildMusicianDashboard(User $user, array $flags): ?array
    {
        if (!$flags['is_musician'] && !$flags['is_admin']) {
            return null;
        }

        $musicianWeddings = $flags['is_musician']
            ? $this->indexWeddingsById($user->getWeddingsAsMusicians()->toArray())
            : $this->indexWeddingsById($this->weddingRepository->findAll());

        $today = new DateTimeImmutable('today');
        $upcoming = array_filter($musicianWeddings, static function (Wedding $wedding) use ($today): bool {
            $date = $wedding->getDate();
            return $date instanceof \DateTimeInterface && $date >= $today;
        });

        usort($upcoming, static function (Wedding $a, Wedding $b): int {
            $dateA = $a->getDate();
            $dateB = $b->getDate();
            return match (true) {
                $dateA === null && $dateB === null => 0,
                $dateA === null => 1,
                $dateB === null => -1,
                default => $dateA <=> $dateB,
            };
        });

        $upcomingSummaries = array_map(function (Wedding $wedding): array {
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
                'viewUrl' => $this->urlGenerator->generate('app_wedding_view', ['id' => $wedding->getId()]),
                'entity' => $wedding,
            ];
        }, array_slice($upcoming, 0, 3));

        $totalAmount = array_reduce($musicianWeddings, static function (float $sum, Wedding $wedding): float {
            return $sum + (float) ($wedding->getMontantTotal() ?? 0);
        }, 0.0);

        return [
            'upcoming' => $upcomingSummaries,
            'totalAmount' => $totalAmount,
            'weddingCount' => count($musicianWeddings),
            'credits' => $user->getCredits(),
        ];
    }

    /**
     * @param array{is_admin: bool, is_parish: bool} $flags
     */
    private function buildParishDashboard(User $user, array $flags): ?array
    {
        if (!$flags['is_parish'] && !$flags['is_admin']) {
            return null;
        }

        $parishWeddings = $flags['is_parish']
            ? $this->indexWeddingsById($user->getWeddingsAsParish()->toArray())
            : $this->indexWeddingsById($this->weddingRepository->findAll());

        $today = new DateTimeImmutable('today');
        $upcoming = array_filter($parishWeddings, static function (Wedding $wedding) use ($today): bool {
            $date = $wedding->getDate();
            return $date instanceof \DateTimeInterface && $date >= $today;
        });

        usort($upcoming, static function (Wedding $a, Wedding $b): int {
            $dateA = $a->getDate();
            $dateB = $b->getDate();
            return match (true) {
                $dateA === null && $dateB === null => 0,
                $dateA === null => 1,
                $dateB === null => -1,
                default => $dateA <=> $dateB,
            };
        });

        $summaries = array_map(function (Wedding $wedding): array {
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
                'viewUrl' => $this->urlGenerator->generate('app_wedding_view', ['id' => $wedding->getId()]),
                'entity' => $wedding,
            ];
        }, array_slice($upcoming, 0, 3));

        return [
            'upcoming' => $summaries,
            'weddingCount' => count($parishWeddings),
            'credits' => $user->getCredits(),
        ];
    }

    /**
     * @param array{is_admin: bool} $flags
     */
    private function buildAdminDashboard(array $flags): ?array
    {
        if (!$flags['is_admin']) {
            return null;
        }

        $today = new DateTimeImmutable('today');
        $upcomingGlobal = $this->weddingRepository->findBy([], ['date' => 'ASC'], 10);
        $upcomingGlobal = array_values(array_filter($upcomingGlobal, static function (Wedding $wedding) use ($today): bool {
            $date = $wedding->getDate();
            return $date instanceof \DateTimeInterface && $date >= $today;
        }));
        $upcomingGlobal = array_slice($upcomingGlobal, 0, 5);

        return [
            'userCount' => $this->userRepository->count([]),
            'weddingCount' => $this->weddingRepository->count([]),
            'upcomingGlobal' => array_map(function (Wedding $wedding): array {
                return [
                    'id' => $wedding->getId(),
                    'title' => $this->formatWeddingTitle($wedding),
                    'date' => $wedding->getDate()?->format('d/m/Y'),
                    'viewUrl' => $this->urlGenerator->generate('app_wedding_view', ['id' => $wedding->getId()]),
                    'entity' => $wedding,
                ];
            }, $upcomingGlobal),
        ];
    }

    /**
     * @param iterable<Wedding> $weddings
     * @return array<int, Wedding>
     */
    private function indexWeddingsById(iterable $weddings): array
    {
        $indexed = [];
        foreach ($weddings as $wedding) {
            if ($wedding instanceof Wedding && $wedding->getId()) {
                $indexed[$wedding->getId()] = $wedding;
            }
        }

        return $indexed;
    }

    private function formatWeddingTitle(Wedding $wedding): string
    {
        $groom = $this->formatParticipantName($wedding->getMarie(), 'Marié à inviter');
        $bride = $this->formatParticipantName($wedding->getMariee(), 'Mariée à inviter');

        return match (true) {
            $groom && $bride => sprintf('Mariage de %s & %s', $groom, $bride),
            $groom => sprintf('Mariage de %s', $groom),
            $bride => sprintf('Mariage de %s', $bride),
            $wedding->getChurch() !== null => sprintf('Mariage à %s', $wedding->getChurch()),
            default => 'Mariage sans couple identifié',
        };
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
}
