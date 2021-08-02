<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience\ValueObject;

use DateTimeImmutable;

class ExperienceItem
{
    private const DECREASE_BAD = 0.9;
    private const DECREASE_GOOD = 0.8;
    private const DECREASE_GOOD_DAYS = 7;
    public const INCREASE_BAD = 5;
    public const INCREASE_GOOD = 1;
    public const KNOWN_RATIO = 3;

    private string $key;
    private int $bad;
    private int $good;
    private DateTimeImmutable $updated;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->bad = 0;
        $this->good = 0;
        $this->updated = $this->getCurrentDate();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function addGood(int $score = self::INCREASE_GOOD): void
    {
        $this->update();
        $this->good += $score;
        $this->bad = (int) ($this->bad * self::DECREASE_BAD);
    }

    public function getGood(): float
    {
        return $this->good;
    }

    public function getGoodRatio(): int
    {
        return (int) ($this->good / ($this->bad + 1));
    }

    public function addBad(int $score = self::INCREASE_BAD): void
    {
        $this->update();
        $this->bad += $score;
    }

    public function getBad(): float
    {
        return $this->bad;
    }

    public function getBadRatio(): int
    {
        return (int) ($this->bad / ($this->good + 1));
    }

    /**
     * Whether an experience item can be considered as know as therefore be ignored.
     */
    public function isKnow(): bool
    {
        return $this->getGoodRatio() >= self::KNOWN_RATIO;
    }

    public function update(): void
    {
        $currentDate = $this->getCurrentDate();
        $days = $currentDate->diff($this->updated)->days;
        // No need to update anything if were still on the same day.
        if ($days === 0) {
            return;
        }

        $this->good = (int) ($this->good * self::DECREASE_GOOD ** ($days / self::DECREASE_GOOD_DAYS));
        $this->updated = $currentDate;
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(): array
    {
        return [
            'good' => $this->good,
            'bad' => $this->bad,
            'updated' => $this->updated->format('Y-m-d'),
        ];
    }

    /**
     * @param array<string, mixed> $values
     */
    public function unserialize(array $values): self
    {
        $this->good = (int) $values['good'];
        $this->bad = (int) $values['bad'];
        // Prevent timezone issues.
        $updated = "{$values['updated']} 00:00:00";
        $this->updated = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $updated) ?: $this->getCurrentDate();

        return $this;
    }

    private function getCurrentDate(): DateTimeImmutable
    {
        return new DateTimeImmutable('today midnight');
    }
}
