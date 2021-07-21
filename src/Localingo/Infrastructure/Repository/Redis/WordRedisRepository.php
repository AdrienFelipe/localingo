<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Word\WordRepositoryInterface;
use Predis\Client;

class WordRedisRepository implements WordRepositoryInterface
{
    public const WORD_INDEX = 'word';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function getRandomAsList(int $count): array
    {
        return (array)$this->redis->srandmember(self::WORD_INDEX, $count);
    }
}
