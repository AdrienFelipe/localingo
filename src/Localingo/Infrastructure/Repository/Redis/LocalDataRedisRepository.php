<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\LocalData\LocalDataRepositoryInterface;

class LocalDataRedisRepository extends RedisRepository implements LocalDataRepositoryInterface
{
    public function clearAllData(): void
    {
        $this->redis->flushall();
    }

    public function saveFileHash(string $filename, string $fileHash): void
    {
        $this->redis->set($this->hashKey($filename), $fileHash);
    }

    public function loadFileHash(string $filename): string
    {
        return (string) $this->redis->get($this->hashKey($filename));
    }

    private function hashKey(string $filename): string
    {
        return "file:$filename:hash";
    }
}
