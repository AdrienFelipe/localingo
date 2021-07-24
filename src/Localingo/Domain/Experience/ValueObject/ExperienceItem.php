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

    public function addBad(float $score = 1): void
    {
        $this->update();
        $this->bad += $score;
    }

    public function update(): void
    {
        $days = (new \DateTimeImmutable())->diff($this->updated)->days;
        $this->good *= self::FACTOR_GOOD ** ($days / self::FACTOR_GOOD_DAYS);
        $this->updated = new \DateTimeImmutable();
    }
}
