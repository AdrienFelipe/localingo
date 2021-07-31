<?php

namespace App\Shared\Application\Test;

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
