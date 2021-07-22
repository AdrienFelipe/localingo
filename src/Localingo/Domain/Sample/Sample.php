<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

class Sample
{
    private string $declined;
    private string $declination;
    private string $number;
    private string $gender;
    private string $word;
    private string $translation;
    private string $state;
    private ?string $case;

    public function __construct(
        string $declined,
        string $declination,
        string $number,
        string $gender,
        string $word,
        string $translation,
        string $state,
        ?string $case,
    ) {
        $this->declined = $declined;
        $this->declination = $declination;
        $this->number = $number;
        $this->gender = $gender;
        $this->word = $word;
        $this->translation = $translation;
        $this->state = $state;
        $this->case = $case;
    }

    public function getDeclined(): string
    {
        return $this->declined;
    }

    public function getDeclination(): string
    {
        return $this->declination;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function getTranslation(): string
    {
        return $this->translation;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getCase(): ?string
    {
        return $this->case;
    }

    public function setDeclined(string $declined): void
    {
        $this->declined = $declined;
    }

    public function setDeclination(string $declination): void
    {
        $this->declination = $declination;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function setWord(string $word): void
    {
        $this->word = $word;
    }

    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function setCase(?string $case): void
    {
        $this->case = $case;
    }
}
