<?php

namespace App\Tests\Localingo\Domain\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ExperienceRepositoryTest extends ApplicationTestCase
{
    private ExperienceRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(ExperienceRepositoryInterface::class);
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
    public function testSaveLoad(Experience $expected): void
    {
        // Save experience.
        $this->repository->save($expected);

        // Load and compare experience.
        $experience = $this->repository->load($expected->getUser());
        ExperienceProvider::assertEquals($this, $expected, $experience);
    }
}
