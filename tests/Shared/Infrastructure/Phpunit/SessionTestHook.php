<?php

namespace App\Tests\Shared\Infrastructure\Phpunit;

use App\Shared\Domain\Session\SessionInterface;
use PHPUnit\Runner\BeforeTestHook;

class SessionTestHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        /** @var SessionInterface $session */
        $session = ApplicationTestCase::service(SessionInterface::class);
        $session->clear();
    }
}
