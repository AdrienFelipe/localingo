<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Application\Declination\DeclinationSelect;
use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;

class SampleBuildCollection
{
    private WordRepositoryInterface $wordRepository;
    private DeclinationRepositoryInterface $declinationRepository;
    private SampleRepositoryInterface $sampleRepository;
    private DeclinationSelect $declinationSelect;

    public function __construct(WordRepositoryInterface $wordRepository, DeclinationRepositoryInterface $declinationRepository, SampleRepositoryInterface $sampleRepository, DeclinationSelect $declinationSelect)
    {
        $this->wordRepository = $wordRepository;
        $this->declinationRepository = $declinationRepository;
        $this->sampleRepository = $sampleRepository;
        $this->declinationSelect = $declinationSelect;
    }

    public function build(Experience $experience, int $count): SampleCollection
    {
        $count = 5;
        $declinations = $this->declinationSelect->mostRelevant($experience);
        $words = $this->wordRepository->getRandomAsList($count);

        return $this->sampleRepository->fromDeclinationAndWords($declinations, $words);
    }
}
