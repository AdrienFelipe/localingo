<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience\ValueObject;

class ExperienceItem
{
    private const FACTOR_BAD = 0.7;
    private const FACTOR_GOOD = 0.8;
    private const FACTOR_GOOD_DAYS = 7;

    private string $key;
    private float $bad;
    private float $good;
    private \DateTimeImmutable $updated;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->bad = 0;
        $this->good = 0;
        $this->updated = new \DateTimeImmutable();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function addGood(float $score = 1): void
    {
        $this->update();
        $this->good += $score;
        $this->bad *= self::FACTOR_BAD;
    }

    public function getGood(): float
    {
        return $this->good;
    }

    public function addBad(float $score = 1): void
    {
        $this->update();
        $this->bad += $score;
    }

    public function getBad(): float
    {
        return $this->bad;
    }

    public function getScore(): int
    {
        return (int) ($this->bad / ($this->good + 1));
    }

    public function update(): void
    {
        $currentDate = $this->getCurrentDate();
        $days = $currentDate->diff($this->updated)->days;
        // No need to update anything if were still on the same day.
        if ($days === 0) {
            return;
        }

        $this->good *= self::FACTOR_GOOD ** ($days / self::FACTOR_GOOD_DAYS);
        $this->updated = $currentDate;
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(): array
    {
        return [
            'good' => (int) ceil($this->good),
            'bad' => (int) ceil($this->bad),
            'updated' => $this->updated->format('Y-m-d'),
        ];
    }

    /**
     * @param array<string, mixed> $values
     */
    public function unserialize(array $values): self
    {
        $this->good = (float) $values['good'];
        $this->bad = (float) $values['bad'];
        $updated = (string) $values['updated'];
        $this->updated = \DateTimeImmutable::createFromFormat('Y-m-d', $updated) ?: $this->getCurrentDate();

        return $this;
    }

    private function getCurrentDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('today midnight');
    }
}
