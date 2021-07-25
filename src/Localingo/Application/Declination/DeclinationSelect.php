<?php

declare(strict_types=1);

namespace App\Localingo\Application\Declination;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItemCollection;

class DeclinationSelect
{
    private const DECLINATIONS_PER_EPISODE = 3;
    private DeclinationRepositoryInterface $declinationRepository;

    public function __construct(DeclinationRepositoryInterface $declinationRepository)
    {
        $this->declinationRepository = $declinationRepository;
    }

    /**
     * @return string[]
     */
    public function mostRelevant(Experience $experience): array
    {
        $declinationExperiences = $experience->getDeclinationExperiences();
        // First update all items based on current date.
        $declinationExperiences->update();

        $currentlyKnown = $this->getCurrentlyKnown($declinationExperiences);
        $revisionNeeded = $this->getRevisionNeeded($declinationExperiences, self::DECLINATIONS_PER_EPISODE);
        $exclude = array_merge($currentlyKnown, $revisionNeeded);
        $limit = self::DECLINATIONS_PER_EPISODE - count($revisionNeeded);
        if ($limit > 0) {
            $revisionNeeded = array_merge($this->declinationRepository->getByPriority($limit, $exclude), $revisionNeeded);
        }

        return $revisionNeeded;
    }

    /**
     * Get all values which bad/good ratio if greater than zero.
     * Sorted from 'worst' to 'best'.
     *
     * @psalm-suppress MixedReturnTypeCoercion
     *
     * @return string[]
     */
    private function getRevisionNeeded(ExperienceItemCollection $itemCollection, int $limit): array
    {
        /** @var string[] $support */
        $support = [];
        foreach ($itemCollection->getIterator() as $key => $item) {
            $value = round($item->getBad() / ($item->getGood() + 1));
            $value <= 0 or $support[$key] = $value;
        }
        arsort($support);

        return array_slice(array_keys($support), 0, $limit);
    }

    /**
     * @return string[]
     */
    private function getCurrentlyKnown(ExperienceItemCollection $itemCollection): array
    {
        /** @var string[] $support */
        $support = [];
        foreach ($itemCollection->getIterator() as $key => $item) {
            if (!$item->getBad() && $item->getGood()) {
                $support[] = $key;
            }
        }

        return $support;
    }
}
