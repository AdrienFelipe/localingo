<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience;

use App\Localingo\Domain\Experience\Exception\ExperienceVersionException;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItem;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItemCollection;
use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\User\User;

class Experience
{
    private const VERSION = 1;

    private int $version;
    private User $user;
    private ExperienceItemCollection $declinationExperiences;
    private ExperienceItemCollection $wordExperiences;
    private ExperienceItemCollection $sampleExperiences;
    private ExperienceItemCollection $caseExperiences;

    public function __construct(User $user)
    {
        $this->version = self::VERSION;
        $this->user = $user;
        $this->declinationExperiences = new ExperienceItemCollection();
        $this->wordExperiences = new ExperienceItemCollection();
        $this->sampleExperiences = new ExperienceItemCollection();
        $this->caseExperiences = new ExperienceItemCollection();
    }

    /**
     * Make sure only up to date entities are unserialized.
     *
     * @throws ExperienceVersionException
     */
    public function __wakeup(): void
    {
        if ($this->version !== self::VERSION) {
            throw new ExperienceVersionException();
        }
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addGood(Sample $sample): void
    {
        $this->getDeclinationExperience($sample)->addGood();
        $this->getWordExperience($sample)->addGood();
        $this->getSampleExperience($sample)->addGood();
        $this->getCaseExperience($sample)->addGood();
    }

    public function addBad(Sample $sample, float $score = 1): void
    {
        $this->getDeclinationExperience($sample)->addBad($score);
        $this->getWordExperience($sample)->addBad($score);
        $this->getSampleExperience($sample)->addBad($score);
        $this->getCaseExperience($sample)->addBad($score);
    }

    private function getDeclinationExperience(Sample $sample): ExperienceItem
    {
        return $this->declinationExperiences->getOrAdd($sample->getDeclination());
    }

    private function getWordExperience(Sample $sample): ExperienceItem
    {
        return $this->wordExperiences->getOrAdd($sample->getWord());
    }

    private function getSampleExperience(Sample $sample): ExperienceItem
    {
        $key = "{$sample->getWord()}:{$sample->getDeclination()}:{$sample->getNumber()}";

        return $this->sampleExperiences->getOrAdd($key);
    }

    private function getCaseExperience(Sample $sample): ExperienceItem
    {
        return $this->caseExperiences->getOrAdd($sample->getCase());
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(): array
    {
        return [
            'user' => $this->user->getId(),
            'version' => $this->version,
            'declinations' => $this->declinationExperiences->serializeArray(),
            'words' => $this->wordExperiences->serializeArray(),
            'samples' => $this->sampleExperiences->serializeArray(),
            'cases' => $this->caseExperiences->serializeArray(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function unserialize(array $data): self
    {
        $this->version = (int) ($data['version'] ?? 0);

        /** @var array<string, array> $items */
        $items = (array) ($data['declinations'] ?? []);
        $this->declinationExperiences = (new ExperienceItemCollection())->unserializeArray($items);

        /** @var array<string, array> $items */
        $items = (array) ($data['words'] ?? []);
        $this->wordExperiences = (new ExperienceItemCollection())->unserializeArray($items);

        /** @var array<string, array> $items */
        $items = (array) ($data['samples'] ?? []);
        $this->sampleExperiences = (new ExperienceItemCollection())->unserializeArray($items);

        /** @var array<string, array> $items */
        $items = (array) ($data['samples'] ?? []);
        $this->caseExperiences = (new ExperienceItemCollection())->unserializeArray($items);

        return $this;
    }
}
