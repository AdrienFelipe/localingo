<?php

declare(strict_types=1);

namespace App\Localingo\Application\Sample;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;

class SampleBuildCollection
{
    private WordRepositoryInterface $wordRepository;
    private DeclinationRepositoryInterface $declinationRepository;
    private SampleRepositoryInterface $sampleRepository;

    public function __construct(WordRepositoryInterface $wordRepository, DeclinationRepositoryInterface $declinationRepository, SampleRepositoryInterface $sampleRepository)
    {
        $this->wordRepository = $wordRepository;
        $this->declinationRepository = $declinationRepository;
        $this->sampleRepository = $sampleRepository;
    }

    public function __invoke(int $count): SampleCollection
    {
        $declination = $this->declinationRepository->getRandom();
        $words = $this->wordRepository->getRandomAsList($count);

        return $this->sampleRepository->fromDeclinationAndWords($declination, $words);
    }
}
