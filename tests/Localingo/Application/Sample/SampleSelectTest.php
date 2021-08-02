<?php

namespace App\Tests\Localingo\Application\Sample;

use App\Localingo\Application\Dataset\DatasetInitialize;
use App\Localingo\Application\Sample\SampleSelect;
use App\Localingo\Domain\Dataset\Exception\DatasetDirectoryException;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItem;
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
    public function testForEpisode(): void
    {
        $this->datasetInitialize->load();
        $goodSamples = $this->sampleRepository->loadMultiple(0, null, ['doktor', 'ręka']);
        $badSamples = $this->sampleRepository->loadMultiple(0, null, ['kobieta', 'widelec']);
        $experience = ExperienceProvider::fromSamples($goodSamples, $badSamples, ExperienceItem::KNOWN_RATIO);

        $samples = $this->sampleSelect->forEpisode($experience, 2, 2);
        self::assertCount(2, $samples);

        $samples = $this->sampleSelect->forEpisode($experience, 20, 20);
        self::assertCount(11, $samples);

        $samples = $this->sampleSelect->forEpisode($experience, 1, 3);
        self::assertCount(3, $samples);
    }

    public function testAddSamplesFromExperience(): void
    {
        $this->datasetInitialize->load();
        $samples = $this->sampleRepository->loadMultiple(0, null, ['doktor', 'ręka']);
        $badSamples = $this->sampleRepository->loadMultiple(0, null, ['kobieta', 'widelec']);
        $experience = ExperienceProvider::fromSamples($samples, $badSamples, ExperienceItem::KNOWN_RATIO);

        $remaining = $this->sampleSelect->addSamplesFromExperience($samples, 10, $experience, []);
        self::assertGreaterThanOrEqual(0, $remaining, 'Remaining value must be 0 or positive');
    }
}
