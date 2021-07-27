<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use Predis\Client;

class SampleCaseRedisRepository implements SampleCaseRepositoryInterface
{
    private const SAMPLE_INDEX = 'cases';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveFromRawData(array $data): void
    {
        // TODO: add proper checks.
        $value = self::valuePattern(
             $data[self::FILE_DECLINATION],
             $data[self::FILE_GENDER],
             $data[self::FILE_NUMBER],
             $data[self::FILE_CASE],
        );
        $this->redis->sadd(self::SAMPLE_INDEX, [$value]);
    }

    public static function valuePattern(mixed $declinations, mixed $gender = null, mixed $numbers = null, mixed $cases = null): string
    {
        return implode(':', [
            self::valuePatternItem($declinations),
            self::valuePatternItem($gender),
            self::valuePatternItem($numbers),
            self::valuePatternItem($cases),
        ]);
    }

    private static function valuePatternItem(mixed $item): string
    {
        if (!$item) {
            return '[^:]*';
        }

        if (is_array($item)) {
            return '('.implode('|', $item).')';
        }

        return (string) $item;
    }
}
