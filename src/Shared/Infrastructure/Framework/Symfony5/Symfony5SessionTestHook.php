<?php

namespace App\Shared\Infrastructure\Framework\Symfony5;

use App\Shared\Application\Test\ApplicationTestCase;
use App\Shared\Domain\Session\SessionInterface;
use PHPUnit\Runner\BeforeTestHook;

class Symfony5SessionTestHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        /** @var SessionInterface $session */
        $session = ApplicationTestCase::service(SessionInterface::class);
        $session->clear();
    }
}
