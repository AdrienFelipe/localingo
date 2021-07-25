<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;

class SampleSelect
{
    private SampleRepositoryInterface $sampleRepository;

    public function __construct(SampleRepositoryInterface $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * @param string[] $words
     */
    public function getRevisionNeeded(Experience $experience, int $count, array $words = []): SampleCollection
    {
        $experiences = $experience->getCaseExperiences();
        // First update all items based on current date.
        $experiences->update();

        $sampleFilters = new SampleCollection();
        // Select most relevant items first.
        foreach ($experiences->getRevisionNeeded($count) as $case) {
            $sampleFilters->append(Experience::caseToSample($case));
        }

        return $this->sampleRepository->fromSampleFilters($sampleFilters, $count, $words);
    }
}
