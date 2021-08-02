<?php

namespace App\Localingo\Application\Sample;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Sample\ValueObject\SampleCaseFilters;

class SampleCaseSelect
{
    private SampleRepositoryInterface $sampleRepository;

    public function __construct(SampleRepositoryInterface $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * @param string[] $words
     */
    public function samplesFromExperience(Experience $experience, int $count, SampleCollection $exclude, array $words = []): SampleCollection
    {
        $filters = $this->buildSampleFilters($experience);
        $samples = [];
        foreach ($filters->getSamples() as $key => $filter) {
            $limit = $count - count($samples);
            $results = $this->sampleRepository->loadMultiple(
                $limit,
                $exclude,
                $words,
                $filter->getDeclination(),
                $filter->getGender(),
                $filter->getNumber(),
                array_unique($filters->getCases($key)),
            );
            array_push($samples, ...$results->getArrayCopy());
        }

        return new SampleCollection(array_slice($samples, 0, $count));
    }

    private function buildSampleFilters(Experience $experience): SampleCaseFilters
    {
        // Use case experiences.
        $experiences = $experience->getCaseExperiences();
        // First update all items based on current date.
        $experiences->update();

        $filters = new SampleCaseFilters();
        foreach ($experiences->getRevisionNeeded() as $case) {
            $filters->add(Experience::caseToSample($case));
        }

        return $filters;
    }
}
