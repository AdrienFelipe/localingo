<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\SampleCharRepositoryInterface;

class SampleCharRedisRepository extends RedisRepository implements SampleCharRepositoryInterface
{
    private const SAMPLE_INDEX = 'chars';

    public function saveFromRawData(array $data): void
    {
        // Focus on values that will have to be written.
        $chars = $data[self::FILE_WORD].$data[self::FILE_TRANSLATION].$data[self::FILE_DECLINED];
        // Keep only letters which are outside of usual keyboards scope.
        $chars = preg_replace('#[ -/a-z]#', '', $chars);
        if ($chars = array_unique(mb_str_split($chars))) {
            $this->redis->sadd(self::SAMPLE_INDEX, $chars);
        }
    }

    public function loadList(): array
    {
        $values = (array) $this->redis->smembers(self::SAMPLE_INDEX);

        return array_filter($values, static function ($value) {return is_string($value); });
    }
}
