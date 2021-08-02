<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;

class SampleRedisRepository extends RedisRepository implements SampleRepositoryInterface
{
    private const SAMPLE_INDEX = 'sample';

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
        /** @var string[]|null[] $data */
        $data = (array) $this->redis->hmget($key, $fields);
        $data = self::unescapeSeparator($data);
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

    public function loadMultiple(int $limit = 0, ?SampleCollection $exclude = null, mixed $words = null, mixed $declinations = null, mixed $genders = null, mixed $numbers = null, mixed $cases = null): SampleCollection
    {
        // Transform excluded samples into a list of strings.
        !$exclude or $exclude = array_map(static function (Sample $sample) {
            return self::keyPattern(false, $sample->getWord(), $sample->getDeclination(), $sample->getGender(), $sample->getNumber(), $sample->getCase());
        }, $exclude->getArrayCopy());

        $keyPattern = self::keyPattern(true, $words, $declinations, $genders, $numbers, $cases);
        /** @var ?string[] $exclude */
        $keys = self::findKeys($this->redis, $keyPattern, $limit, $exclude ?? []);

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
}
