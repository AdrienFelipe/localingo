<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use Predis\Client;

class SampleRedisRepository implements SampleRepositoryInterface
{
    private const SAMPLE_INDEX = 'sample';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveFromRawData(array $data): void
    {
        // TODO: add proper checks.
        $key = self::keyPattern(
            $data[self::FILE_WORD],
            $data[self::FILE_DECLINATION],
            $data[self::FILE_GENDER],
            $data[self::FILE_NUMBER],
            $data[self::FILE_CASE],
        );
        $this->redis->hmset($key, $data);
    }

    public function load(string $key): Sample
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
        // Redis empty strings are returned as null values.
        $data = array_map(static function (?string $field) {
            return $field ?? '';
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
        $key_pattern = self::keyPattern($words, $declinations);
        $keys = RedisTools::findKeys($this->redis, $key_pattern, $limit);

        $samples = [];
        foreach ($keys as $key) {
            $samples[] = $this->load($key);
        }

        return new SampleCollection($samples);
    }

    public static function keyPattern(mixed $words, mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): string
    {
        return implode(':', [
            self::SAMPLE_INDEX,
            self::keyPatternItem($words),
            self::keyPatternItem($declinations),
            self::keyPatternItem($genders),
            self::keyPatternItem($numbers),
            self::keyPatternItem($cases),
        ]);
    }

    private static function keyPatternItem(mixed $item): string
    {
        if (!$item) {
            return '[^:]*';
        }

        if (is_array($item)) {
            return '('.implode('|', $item).')';
        }

        return (string) $item;
    }

    public function fromSampleFilters(SampleCollection $sampleFilters, int $count, array $words = []): SampleCollection
    {
        // Keep track of the total without counting on each iteration.
        $totalSamples = 0;
        $samples = new SampleCollection();
        foreach ($sampleFilters as $sampleFilter) {
            $key_pattern = self::keyPattern(
                $words ?: $sampleFilter->getWord(),
                $sampleFilter->getDeclination(),
                $sampleFilter->getGender(),
                $sampleFilter->getNumber(),
                $sampleFilter->getCase()
            );

            foreach (RedisTools::findKeys($this->redis, $key_pattern, $count) as $key) {
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
