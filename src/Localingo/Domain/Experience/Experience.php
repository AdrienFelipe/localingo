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

    private const KEY_USER = 'user';
    private const KEY_VERSION = 'version';
    private const KEY_DECLINATIONS = 'declinations';
    private const KEY_WORDS = 'words';
    private const KEY_CASES = 'cases';

    private int $version;
    private User $user;
    private ExperienceItemCollection $declinationExperiences;
    private ExperienceItemCollection $wordExperiences;
    private ExperienceItemCollection $caseExperiences;

    public function __construct(User $user)
    {
        $this->version = self::VERSION;
        $this->user = $user;
        $this->declinationExperiences = new ExperienceItemCollection();
        $this->wordExperiences = new ExperienceItemCollection();
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

    public function getDeclinationExperiences(): ExperienceItemCollection
    {
        return $this->declinationExperiences;
    }

    public function getWordExperiences(): ExperienceItemCollection
    {
        return $this->wordExperiences;
    }

    public function getCaseExperiences(): ExperienceItemCollection
    {
        return $this->caseExperiences;
    }

    public function addGood(Sample $sample): void
    {
        $this->declinationItem($sample)->addGood();
        $this->wordItem($sample)->addGood();
        $this->caseItem($sample)->addGood();
    }

    public function addBad(Sample $sample, float $score = 1): void
    {
        $this->declinationItem($sample)->addBad($score);
        $this->wordItem($sample)->addBad($score);
        $this->caseItem($sample)->addBad($score);
    }

    private function declinationItem(Sample $sample): ExperienceItem
    {
        return $this->declinationExperiences->getOrAdd($sample->getDeclination());
    }

    private function wordItem(Sample $sample): ExperienceItem
    {
        return $this->wordExperiences->getOrAdd($sample->getWord());
    }

    private function caseItem(Sample $sample): ExperienceItem
    {
        $key = $this->caseKeyPattern(
            $sample->getDeclination(),
            $sample->getGender(),
            $sample->getNumber(),
            $sample->getCase()
        );

        return $this->caseExperiences->getOrAdd($key);
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(): array
    {
        return [
            self::KEY_USER => $this->user->getId(),
            self::KEY_VERSION => $this->version,
            self::KEY_DECLINATIONS => $this->declinationExperiences->serializeArray(),
            self::KEY_WORDS => $this->wordExperiences->serializeArray(),
            self::KEY_CASES => $this->caseExperiences->serializeArray(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function unserialize(array $data): self
    {
        $this->version = (int) ($data[self::KEY_VERSION] ?? 0);

        /** @var array<string, array> $items */
        $items = (array) ($data[self::KEY_DECLINATIONS] ?? []);
        $this->declinationExperiences->unserializeArray($items);

        /** @var array<string, array> $items */
        $items = (array) ($data[self::KEY_WORDS] ?? []);
        $this->wordExperiences->unserializeArray($items);

        /** @var array<string, array> $items */
        $items = (array) ($data[self::KEY_CASES] ?? []);
        $this->caseExperiences->unserializeArray($items);

        return $this;
    }

    private function caseKeyPattern(?string $declination, ?string $gender, ?string $number, ?string $case): string
    {
        $emptyPattern = '[^:]*';

        return implode(':', [
            $declination ?? $emptyPattern,
            $gender ?? $emptyPattern,
            $number ?? $emptyPattern,
            $case ?? $emptyPattern,
        ]);
    }
}
