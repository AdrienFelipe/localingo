<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Application\Declination\DeclinationSelect;
use App\Localingo\Application\Word\WordSelect;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;

class SampleSelect
{
    private SampleRepositoryInterface $sampleRepository;
    private SampleCaseRepositoryInterface $caseRepository;
    private DeclinationSelect $declinationSelect;
    private WordSelect $wordSelect;

    public function __construct(
        SampleRepositoryInterface $sampleRepository,
        SampleCaseRepositoryInterface $caseRepository,
        DeclinationSelect $declinationSelect,
        WordSelect $wordSelect
    ) {
        $this->sampleRepository = $sampleRepository;
        $this->caseRepository = $caseRepository;
        $this->declinationSelect = $declinationSelect;
        $this->wordSelect = $wordSelect;
    }

    public function forEpisode(Experience $experience, int $declinationsCount, int $wordsCount, int $samplesCount): SampleCollection
    {
        // Select most relevant declinations to train on.
        $declinations = $this->declinationSelect->mostRelevant($experience, $declinationsCount);
        // Add to experience all cases of selected declinations.
        $this->addCasesToExperience($experience, $declinations);

        // Get most relevant words to train.
        $words = $this->wordSelect->mostRelevant($experience, $wordsCount);
        // Sample from experience the most relevant cases, limited to chosen words.
        $samples = $this->getRevisionNeededCases($experience, $samplesCount, $words);

        // If not enough yet, add more selected cases without filtering words.
        if ($remaining = $samplesCount - count($samples)) {
            // TODO: handle possible duplicates from previous selection.
            foreach ($this->getRevisionNeededCases($experience, $remaining) as $sample) {
                $samples->append($sample);
            }
        }

        return $samples;
    }

    /**
     * @param string[] $words
     */
    private function getRevisionNeededCases(Experience $experience, int $count, array $words = []): SampleCollection
    {
        $experiences = $experience->getCaseExperiences();
        // First update all items based on current date.
        $experiences->update();

        $sampleFilters = new SampleCollection();
        $cases = [];
        $filters = [];
        // Select most relevant items first.
        foreach ($experiences->getRevisionNeeded() as $case) {
            $sampleFilter = Experience::caseToSample($case);
            $key = implode(':', [
                $sampleFilter->getDeclination(),
                $sampleFilter->getGender(),
                $sampleFilter->getNumber(),
            ]);
            if (!isset($cases[$key])) {
                $cases[$key] = [];
                $filters[$key] = $sampleFilter;
            }
            $cases[$key][] = $sampleFilter->getCase();
        }

        $samples = [];
        foreach ($cases as $key => $case) {
            $limit = $count - count($samples);
            $filter = $filters[$key];
            $results = $this->sampleRepository->loadMultiple(
                $limit,
                $words,
                $filter->getDeclination(),
                $filter->getGender(),
                $filter->getNumber(),
                array_unique($case),
            );
            array_push($samples, ...$results->getArrayCopy());
        }

        return new SampleCollection(array_slice($samples, 0, $count));
    }

    private function fromDeclinationCases(Experience $experience, int $declinationsCount, int $remaining): SampleCollection
    {
        // Select most relevant declinations to train on.
        $declinations = $this->declinationSelect->mostRelevant($experience, $declinationsCount);
        // Find all declination's cases.
        $sampleFilters = $this->caseRepository->getCases($declinations);

        return $this->sampleRepository->fromSampleFilters($sampleFilters, $remaining);
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
