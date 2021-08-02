<?php

namespace App\Tests\Localingo\Application\Sample;

use App\Localingo\Application\Dataset\DatasetInitialize;
use App\Localingo\Application\Sample\SampleSelect;
use App\Localingo\Domain\Dataset\Exception\DatasetDirectoryException;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;
use App\Tests\Localingo\Domain\Experience\ExperienceProvider;

class SampleSelectTest extends ApplicationTestCase
{
    private SampleSelect $sampleSelect;
    private DatasetInitialize $datasetInitialize;
    private SampleRepositoryInterface $sampleRepository;

    public function setUp(): void
    {
        $this->sampleSelect = self::service(SampleSelect::class);
        $this->sampleRepository = self::service(SampleRepositoryInterface::class);
        $this->datasetInitialize = self::service(DatasetInitialize::class);
    }

    /**
     * @throws DatasetDirectoryException
     */
    public function testSelect(): void
    {
        $this->datasetInitialize->load();
        $samples = $this->sampleRepository->loadMultiple(6);
        $experience = ExperienceProvider::fromSamples($samples);

        $samples = $this->sampleSelect->forEpisode($experience, 2, 2);
        self::assertCount(2, $samples);

        $samples = $this->sampleSelect->forEpisode($experience, 20, 20);
        self::assertCount(11, $samples);

        $samples = $this->sampleSelect->forEpisode($experience, 1, 3);
        self::assertCount(3, $samples);
    }
}
