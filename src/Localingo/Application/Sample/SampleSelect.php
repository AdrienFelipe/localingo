<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Application\Declination\DeclinationSelect;
use App\Localingo\Application\Word\WordSelect;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;

class SampleSelect
{
    private SampleRepositoryInterface $repository;
    private DeclinationSelect $declinationSelect;
    private WordSelect $wordSelect;

    public function __construct(SampleRepositoryInterface $sampleRepository, DeclinationSelect $declinationSelect, WordSelect $wordSelect)
    {
        $this->repository = $sampleRepository;
        $this->declinationSelect = $declinationSelect;
        $this->wordSelect = $wordSelect;
    }

    public function forEpisode(Experience $experience, int $declinationsCount, int $wordsCount, int $samplesCount): SampleCollection
    {
        // Get most relevant words to train.
        $words = $this->wordSelect->mostRelevant($experience, $wordsCount);
        // Sample most relevant cases from selected declinations and words.
        $samples = $this->getRevisionNeeded($experience, $samplesCount, $words);

        // If not enough yet, add more selected cases without words filtering.
        if ($remaining = $samplesCount - count($samples)) {
            foreach ($this->getRevisionNeeded($experience, $remaining) as $sample) {
                $samples->append($sample);
            }
        }

        // If not enough yet, add any case from selected declinations and words.
        if ($remaining = $samplesCount - count($samples)) {
            // Selection most relevant declinations to train on.
            $declinations = $this->declinationSelect->mostRelevant($experience, $declinationsCount);
            foreach ($this->repository->fromDeclinationAndWords($declinations, $words, $remaining) as $sample) {
                $samples->append($sample);
            }
        }

        return $samples;
    }

    /**
     * @param string[] $words
     */
    private function getRevisionNeeded(Experience $experience, int $count, array $words = []): SampleCollection
    {
        $experiences = $experience->getCaseExperiences();
        // First update all items based on current date.
        $experiences->update();

        $sampleFilters = new SampleCollection();
        // Select most relevant items first.
        foreach ($experiences->getRevisionNeeded($count) as $case) {
            $sampleFilters->append(Experience::caseToSample($case));
        }

        return $this->repository->fromSampleFilters($sampleFilters, $count, $words);
    }
}
