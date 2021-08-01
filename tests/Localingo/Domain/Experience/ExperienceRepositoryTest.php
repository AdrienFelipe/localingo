<?php

namespace App\Tests\Localingo\Domain\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;

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
    public function experienceProvider(): array
    {
        return [
            [ExperienceProvider::empty()],
            [ExperienceProvider::filled()],
        ];
    }

    /**
     * @dataProvider experienceProvider
     */
    public function testSaveLoad(Experience $expected): void
    {
        // Save experience.
        $this->repository->save($expected);

        // Load and compare experience.
        $experience = $this->repository->load($expected->getUser());
        ExperienceProvider::assertEquals($this, $expected, $experience);
    }
}
