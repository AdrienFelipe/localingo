<?php

namespace App\Tests\Localingo\Domain\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ExperienceFileTest extends ApplicationTestCase
{
    private ExperienceFileInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(ExperienceFileInterface::class);
    }

    /**
     * @return Experience[][]
     */
    public static function experienceProvider(): array
    {
        return [
            [ExperienceProvider::empty()],
            [ExperienceProvider::filled()],
        ];
    }

    #[DataProvider('experienceProvider')]
    public function testWriteRead(Experience $expected): void
    {
        // Save experience.
        $this->repository->write($expected);

        // Load and compare experience.
        $experience = $this->repository->read($expected->getUser());
        ExperienceProvider::assertEquals($this, $expected, $experience);
    }
}
