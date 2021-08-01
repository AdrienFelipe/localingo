<?php

namespace App\Tests\Localingo\Application\Experience;

use App\Localingo\Application\Experience\ExperienceGet;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Localingo\Domain\User\User;
use App\Shared\Application\Test\ApplicationTestCase;

class ExperienceGetTest extends ApplicationTestCase
{
    private ExperienceGet $experienceGet;
    private ExperienceRepositoryInterface $repository;
    private ExperienceFileInterface $file;

    public function setUp(): void
    {
        $this->experienceGet = self::service(ExperienceGet::class);
        $this->repository = self::service(ExperienceRepositoryInterface::class);
        $this->file = self::service(ExperienceFileInterface::class);
    }

    public function testGetCurrent(): void
    {
        // From nonexistent user.
        $user = new User('test');
        $experience = $this->experienceGet->current($user);
        self::assertEmpty($experience->getWordExperiences());

        // From local file (only read if nonexistent in repository).
        $experience->getWordExperiences()->getOrAdd('local-file');
        $this->file->write($experience);
        $experience = $this->experienceGet->current($user);
        self::assertArrayHasKey('local-file', $experience->getWordExperiences());

        // From repository.
        $experience->getWordExperiences()->getOrAdd('repository');
        $this->repository->save($experience);
        $experience = $this->experienceGet->current($user);
        self::assertArrayHasKey('repository', $experience->getWordExperiences());
    }
}
