<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Word\WordRepositoryInterface;

class WordRedisRepository extends RedisRepository implements WordRepositoryInterface
{
    private const WORD_INDEX = 'words';

    public function saveFromRawData(array $data): void
    {
        $values = [$data[self::FILE_WORD] => $data[self::FILE_PRIORITY]];
        $this->redis->zadd(self::WORD_INDEX, $values);
    }

    public function getByPriority(int $limit, array $exclude = []): array
    {
        return self::sortedScan($this->redis, self::WORD_INDEX, $limit, $exclude);
    }
}
