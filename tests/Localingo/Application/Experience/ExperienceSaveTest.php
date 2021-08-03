<?php

namespace App\Tests\Localingo\Application\Experience;

use App\Localingo\Application\Experience\ExperienceSave;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Tests\Localingo\Domain\Experience\ExperienceProvider;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class ExperienceSaveTest extends ApplicationTestCase
{
    private ExperienceSave $experienceSave;
    private ExperienceRepositoryInterface $repository;
    private ExperienceFileInterface $file;

    public function setUp(): void
    {
        $this->experienceSave = self::service(ExperienceSave::class);
        $this->repository = self::service(ExperienceRepositoryInterface::class);
        $this->file = self::service(ExperienceFileInterface::class);
    }

    public function testSaveToFile(): void
    {
        $expected = ExperienceProvider::filled();
        $this->experienceSave->toFile($expected);

        $experience = $this->file->read($expected->getUser());
        ExperienceProvider::assertEquals($this, $expected, $experience);
    }

    public function testSaveToRepository(): void
    {
        $expected = ExperienceProvider::filled();
        $this->experienceSave->toRepository($expected);

        $experience = $this->repository->load($expected->getUser());
        ExperienceProvider::assertEquals($this, $expected, $experience);
    }
}
