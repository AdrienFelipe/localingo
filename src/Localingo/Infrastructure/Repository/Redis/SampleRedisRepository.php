<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;

class SampleRedisRepository extends RedisRepository implements SampleRepositoryInterface
{
    private const SAMPLE_INDEX = 'sample';
    private const SEPARATOR = ':';
    private const ESCAPER = ';;';

    public function saveFromRawData(array $data): void
    {
        // TODO: add proper checks.
        $key = self::keyPattern(
            false,
            $data[self::FILE_WORD],
            $data[self::FILE_DECLINATION],
            $data[self::FILE_GENDER],
            $data[self::FILE_NUMBER],
            $data[self::FILE_CASE],
        );
        $this->redis->hmset($key, $data);
    }

    private function load(string $key): Sample
    {
        $fields = [
            self::FILE_DECLINED,
            self::FILE_DECLINATION,
            self::FILE_NUMBER,
            self::FILE_GENDER,
            self::FILE_WORD,
            self::FILE_TRANSLATION,
            self::FILE_STATE,
            self::FILE_CASE,
        ];
        $data = (array) $this->redis->hmget($key, $fields);
        $data = array_map(static function (?string $value) {
            // Put escaped separator back. Redis empty strings are returned as null values.
            return str_replace(self::ESCAPER, self::SEPARATOR, $value ?? '');
        }, $data);
        // Apply fields names.
        $data = array_combine($fields, $data);

        return new Sample(
            $data[self::FILE_DECLINED],
            $data[self::FILE_DECLINATION],
            $data[self::FILE_NUMBER],
            $data[self::FILE_GENDER],
            $data[self::FILE_WORD],
            $data[self::FILE_TRANSLATION],
            $data[self::FILE_STATE],
            $data[self::FILE_CASE],
        );
    }

    public function loadMultiple(int $limit, mixed $words, mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): SampleCollection
    {
        $keyPattern = self::keyPattern(true, $words, $declinations, $genders, $numbers, $cases);
        $keys = self::findKeys($this->redis, $keyPattern, $limit);

        $samples = [];
        foreach ($keys as $key) {
            $samples[] = $this->load($key);
        }

        return new SampleCollection($samples);
    }

    private static function keyPattern(bool $regex, mixed $words, mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): string
    {
        return implode(self::SEPARATOR, [
            self::SAMPLE_INDEX,
            self::keyPatternItem($regex, $words),
            self::keyPatternItem($regex, $declinations),
            self::keyPatternItem($regex, $genders),
            self::keyPatternItem($regex, $numbers),
            self::keyPatternItem($regex, $cases),
        ]);
    }

    private static function keyPatternItem(bool $regex, mixed $item): string
    {
        if (!$item) {
            return $regex ? '[^'.self::SEPARATOR.']*' : '';
        }

        if (is_array($item)) {
            $item = array_map(static function (mixed $value) use ($regex) {
                return self::escapeKeyItem($regex, $value);
            }, $item);

            return '('.implode('|', $item).')';
        }

        return self::escapeKeyItem($regex, $item);
    }

    /**
     * Escape values separator to avoid collisions with internal content.
     * Escape regex special characters for them not to be applied when used in regex mode.
     */
    private static function escapeKeyItem(bool $regex, mixed $value): string
    {
        $value = str_replace(self::SEPARATOR, self::ESCAPER, (string) $value);

        return $regex ? preg_quote($value, '/') : $value;
    }

    public function fromSampleFilters(SampleCollection $sampleFilters, int $count, array $words = []): SampleCollection
    {
        // Keep track of the total without counting on each iteration.
        $totalSamples = 0;
        $samples = new SampleCollection();
        foreach ($sampleFilters as $sampleFilter) {
            $key_pattern = self::keyPattern(
                true,
                $words ?: $sampleFilter->getWord(),
                $sampleFilter->getDeclination(),
                $sampleFilter->getGender(),
                $sampleFilter->getNumber(),
                $sampleFilter->getCase()
            );

            foreach (self::findKeys($this->redis, $key_pattern, $count) as $key) {
                $samples->append($this->load($key));
                // Early exit if all items were found.
                if (++$totalSamples === $count) {
                    break 2;
                }
            }
        }

        return $samples;
    }
}
