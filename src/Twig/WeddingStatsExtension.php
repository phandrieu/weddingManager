<?php

namespace App\Twig;

use App\Entity\Wedding;
use App\Repository\SongTypeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WeddingStatsExtension extends AbstractExtension
{
    public function __construct(
        private readonly SongTypeRepository $songTypeRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wedding_song_stats', [$this, 'getSongStats']),
            new TwigFunction('wedding_field_stats', [$this, 'getFieldStats']),
        ];
    }

    /**
     * Calculate song selection statistics for a wedding
     * Returns: [validated => X, withSong => Y, empty => Z, total => N]
     */
    public function getSongStats(Wedding $wedding): array
    {
        $selections = $wedding->getSongSelections();
        $isMesse = $wedding->isMesse();
        
        $validated = 0;
        $withSong = 0;
        $empty = 0;
        
        $selectionsByType = [];
        foreach ($selections as $selection) {
            $typeId = $selection->getSongType()?->getId();
            if ($typeId) {
                $selectionsByType[$typeId] = $selection;
            }
        }
        
        // Get song types filtered by messe status
        $allTypes = $this->songTypeRepository->findOrderedByCelebrationPeriod($isMesse);
        $totalSongTypes = count($allTypes);
        
        foreach ($allTypes as $type) {
            $typeId = $type->getId();
            if (isset($selectionsByType[$typeId])) {
                $selection = $selectionsByType[$typeId];
                if ($selection->getSong() !== null) {
                    // Has a song selected
                    if ($selection->isValidatedByMusician() && $selection->isValidatedByParish()) {
                        $validated++;
                    } else {
                        $withSong++;
                    }
                } else {
                    $empty++;
                }
            } else {
                $empty++;
            }
        }
        
        return [
            'validated' => $validated,
            'withSong' => $withSong,
            'empty' => $empty,
            'total' => $totalSongTypes,
        ];
    }

    /**
     * Calculate field completion statistics for a wedding
     * Returns: [filled => X, empty => Y, total => N]
     */
    public function getFieldStats(Wedding $wedding): array
    {
        $fields = [
            'date' => $wedding->getDate() !== null,
            'time' => $wedding->getTime() !== null,
            'parish' => !empty($wedding->getParish()),
            'church' => !empty($wedding->getChurch()),
            'addressLine1' => !empty($wedding->getAddressLine1()),
            'addressPostalCodeAndCity' => !empty($wedding->getAddressPostalCodeAndCity()),
            'marie' => $wedding->getMarie() !== null || !empty($wedding->getMarieFirstName()),
            'mariee' => $wedding->getMariee() !== null || !empty($wedding->getMarieeFirstName()),
            'musicians' => $wedding->getMusicians()->count() > 0,
            'parishUsers' => $wedding->getParishUsers()->count() > 0,
            'messe' => true, // Always defined (boolean)
            'paymentOption' => $wedding->getPaymentOption() !== null,
        ];
        
        $filled = array_sum(array_map(fn($v) => $v ? 1 : 0, $fields));
        $total = count($fields);
        
        return [
            'filled' => $filled,
            'empty' => $total - $filled,
            'total' => $total,
        ];
    }
}
