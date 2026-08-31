<?php

namespace App\Tests\Shared\Infrastructure\Phpunit;

use App\Shared\Domain\Session\SessionInterface;

class SessionTestHook extends ClearBeforeTestExtension
{
    protected function clear(): void
    {
        /** @var SessionInterface $session */
        $session = ApplicationTestCase::service(SessionInterface::class);
        $session->clear();
    }
}
