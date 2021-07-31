<?php

namespace App\Shared\Application\Test;

use App\Shared\Domain\Repository\RepositoryInterface;
use PHPUnit\Runner\BeforeTestHook;

class RepositoryTestHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        /** @var RepositoryInterface $repository */
        $repository = ApplicationTestCase::service(RepositoryInterface::class);
        $repository->clear();
    }
}
