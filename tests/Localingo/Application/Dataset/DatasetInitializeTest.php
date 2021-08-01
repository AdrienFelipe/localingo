<?php

declare(strict_types=1);

namespace App\Tests\Localingo\Application\Dataset;

use App\Localingo\Application\Dataset\DatasetInitialize;
use App\Localingo\Domain\Dataset\Exception\DatasetDirectoryException;
use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCharRepositoryInterface;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;

class DatasetInitializeTest extends ApplicationTestCase
{
    private DatasetInitialize $dataset;
    private WordRepositoryInterface $wordRepository;
    private DeclinationRepositoryInterface $declinationRepository;
    private SampleRepositoryInterface $sampleRepository;
    private SampleCaseRepositoryInterface $sampleCaseRepository;
    private SampleCharRepositoryInterface $sampleCharRepository;

    public function setUp(): void
    {
        $this->dataset = self::service(DatasetInitialize::class);
        $this->wordRepository = self::service(WordRepositoryInterface::class);
        $this->declinationRepository = self::service(DeclinationRepositoryInterface::class);
        $this->sampleRepository = self::service(SampleRepositoryInterface::class);
        $this->sampleCaseRepository = self::service(SampleCaseRepositoryInterface::class);
        $this->sampleCharRepository = self::service(SampleCharRepositoryInterface::class);
    }

    /**
     * @throws DatasetDirectoryException
     */
    public function testLoad(): void
    {
        $this->dataset->load();

        self::assertCount(7, $this->wordRepository->getByPriority(0), 'Incorrect words count');
        self::assertCount(6, $this->declinationRepository->getByPriority(), 'Incorrect declinations count');
        self::assertCount(14, $this->sampleRepository->loadMultiple(100, null, null), 'Incorrect samples count');
        self::assertCount(7, $this->sampleCaseRepository->getCases([]), 'Incorrect samplesCases count');
        self::assertCount(1, $this->sampleCharRepository->loadList(), 'Incorrect samplesChars count');
    }
}
