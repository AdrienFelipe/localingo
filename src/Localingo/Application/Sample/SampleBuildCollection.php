<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Application\Declination\DeclinationSelect;
use App\Localingo\Application\Word\WordSelect;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;

class SampleBuildCollection
{
    private DeclinationSelect $declinationSelect;
    private WordSelect $wordSelect;
    private SampleSelect $sampleSelect;
    private SampleRepositoryInterface $sampleRepository;

    public function __construct(DeclinationSelect $declinationSelect, WordSelect $wordSelect, SampleSelect $sampleSelect, SampleRepositoryInterface $sampleRepository)
    {
        $this->declinationSelect = $declinationSelect;
        $this->wordSelect = $wordSelect;
        $this->sampleSelect = $sampleSelect;
        $this->sampleRepository = $sampleRepository;
    }

    public function build(Experience $experience, int $declinationsCount, int $wordsCount, int $samplesCount): SampleCollection
    {
        // Get most relevant words to train.
        $words = $this->wordSelect->mostRelevant($experience, $wordsCount);
        // Sample most relevant cases from selected declinations and words.
        $samples = $this->sampleSelect->getRevisionNeeded($experience, $samplesCount, $words);

        // If not enough yet, add more selected cases without words filtering.
        if ($remaining = $samplesCount - count($samples)) {
            foreach ($this->sampleSelect->getRevisionNeeded($experience, $remaining) as $sample) {
                $samples->append($sample);
            }
        }

        // If not enough yet, add any case from selected declinations and words.
        if ($remaining = $samplesCount - count($samples)) {
            // Selection most relevant declinations to train on.
            $declinations = $this->declinationSelect->mostRelevant($experience, $declinationsCount);
            foreach ($this->sampleRepository->fromDeclinationAndWords($declinations, $words, $remaining) as $sample) {
                $samples->append($sample);
            }
        }

        return $samples;
    }
}
