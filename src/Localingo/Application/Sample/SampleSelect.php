<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Application\Word\WordSelect;
use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCollection;

class SampleSelect
{
    private SampleCaseRepositoryInterface $caseRepository;
    private WordSelect $wordSelect;
    private DeclinationRepositoryInterface $declinationRepository;
    private SampleCaseSelect $sampleCaseSelect;

    public function __construct(
        SampleCaseRepositoryInterface $caseRepository,
        WordSelect $wordSelect,
        DeclinationRepositoryInterface $declinationRepository,
        SampleCaseSelect $sampleCaseSelect
    ) {
        $this->caseRepository = $caseRepository;
        $this->wordSelect = $wordSelect;
        $this->declinationRepository = $declinationRepository;
        $this->sampleCaseSelect = $sampleCaseSelect;
    }

    public function forEpisode(Experience $experience, int $maxWords, int $maxSamples): SampleCollection
    {
        $samples = new SampleCollection();
        // Get most relevant words to train.
        $words = $this->wordSelect->mostRelevant($experience, $maxWords);
        // Add samples that need review, and early exit if max is met.
        if (!$remaining = $this->addSamplesFromExperience($samples, $maxSamples, $experience, $words)) {
            return $samples;
        }

        // Fetch from non trained samples.
        foreach ($this->declinationRepository->getByPriority() as $declination) {
            // Add to experience all cases of selected declination.
            $this->addCasesToExperience($experience, [$declination]);
            if (!$remaining = $this->addSamplesFromExperience($samples, $remaining, $experience, $words)) {
                break;
            }
        }

        return $samples;
    }

    /**
     * @param string[] $words
     *
     * @return int the number of remaining samples to meet the limit
     */
    public function addSamplesFromExperience(SampleCollection $samples, int $limit, Experience $experience, array $words): int
    {
        // Sample from experience the most relevant cases, limited to chosen words first.
        foreach ($this->sampleCaseSelect->samplesFromExperience($experience, $limit, $samples, $words) as $sample) {
            $samples->append($sample);
        }

        // If not enough, add more selected cases without filtering words.
        if ($remaining = $limit - count($samples)) {
            foreach ($this->sampleCaseSelect->samplesFromExperience($experience, $remaining, $samples) as $sample) {
                $samples->append($sample);
                --$remaining;
            }
        }

        return $remaining;
    }

    /**
     * @param string[] $declinations
     */
    private function addCasesToExperience(Experience $experience, array $declinations): void
    {
        // Find all declination's cases.
        $cases = $this->caseRepository->getCases($declinations);
        // Add cases to experience.
        foreach ($cases as $case) {
            // Adds the case sample to the experience if it does not yet exist.
            $experience->caseItem($case);
        }
    }
}
