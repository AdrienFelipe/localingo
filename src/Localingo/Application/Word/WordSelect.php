<?php

declare(strict_types=1);

namespace App\Localingo\Application\Word;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Word\WordRepositoryInterface;

class WordSelect
{
    private WordRepositoryInterface $wordRepository;

    public function __construct(WordRepositoryInterface $wordRepository)
    {
        $this->wordRepository = $wordRepository;
    }

    /**
     * @return string[]
     */
    public function mostRelevant(Experience $experience, int $count): array
    {
        $experiences = $experience->getWordExperiences();
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
            array_push($selection, ...$this->wordRepository->getByPriority($limit, $exclude));
        }

        return $selection;
    }
}
