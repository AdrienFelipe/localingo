<?php

declare(strict_types=1);

namespace App\Localingo\Application\Declination;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Experience\Experience;

class DeclinationSelect
{
    private DeclinationRepositoryInterface $declinationRepository;

    public function __construct(DeclinationRepositoryInterface $declinationRepository)
    {
        $this->declinationRepository = $declinationRepository;
    }

    /**
     * @return string[]
     */
    public function mostRelevant(Experience $experience, int $count): array
    {
        $experiences = $experience->getDeclinationExperiences();
        // First update all items based on current date.
        $experiences->update();

        // Select most relevant items first.
        $selection = $experiences->getRevisionNeeded($count);
        $limit = $count - count($selection);

        // Fill with new items if count is not enough.
        if ($limit > 0) {
            $exclude = $experiences->getCurrentlyKnown();
            // Exclude also selection from new items.
            array_push($exclude, ...$selection);
            array_push($selection, ...$this->declinationRepository->getByPriority($limit, $exclude));
        }

        return $selection;
    }
}