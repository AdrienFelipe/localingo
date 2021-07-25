<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Word\WordRepositoryInterface;
use Predis\Client;

class WordRedisRepository implements WordRepositoryInterface
{
    private const WORD_INDEX = 'words';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveFromRawData(array $data): void
    {
        $values = [$data[self::FILE_WORD] => $data[self::FILE_PRIORITY]];
        $this->redis->zadd(self::WORD_INDEX, $values);
    }

    public function getByPriority(int $limit, array $exclude = []): array
    {
        return RedisTools::sortedScan($this->redis, self::WORD_INDEX, $limit, $exclude);
    }
}
