<?php

namespace App\Localingo\Domain\Sample\ValueObject;

use App\Localingo\Domain\Sample\Sample;

class SampleCaseFilters
{
    /** @var array<string, string[]> */
    private array $cases;
    /** @var array<string, Sample> */
    private array $samples;

    public function __construct()
    {
        $this->cases = [];
        $this->samples = [];
    }

    public function add(Sample $sampleFilter): void
    {
        $key = $this->buildKey($sampleFilter);
        // Handle already used keys.
        if (!isset($this->cases[$key])) {
            $this->cases[$key] = [];
            $this->samples[$key] = $sampleFilter;
        }
        $this->cases[$key][] = $sampleFilter->getCase();
    }

    private function buildKey(Sample $sampleFilter): string
    {
        return implode(':', [
            $sampleFilter->getDeclination(),
            $sampleFilter->getGender(),
            $sampleFilter->getNumber(),
        ]);
    }

    /**
     * @return array<string, Sample>
     */
    public function getSamples(): array
    {
        return $this->samples;
    }

    /**
     * @return string[]
     */
    public function getCases(string $key): array
    {
        return $this->cases[$key] ?? [];
    }
}
