<?php

namespace App\Localingo\Infrastructure\Repository\Redis;

use PHPUnit\Runner\BeforeTestHook;
use Predis\Client;

class RedisRepositoryTestHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        $client = new Client($_ENV['REDIS_DSN']);
        $client->flushall();
    }
}
