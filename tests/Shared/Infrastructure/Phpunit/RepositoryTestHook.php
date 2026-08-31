<?php

namespace App\Tests\Shared\Infrastructure\Phpunit;

use App\Shared\Domain\Repository\RepositoryInterface;

class RepositoryTestHook extends ClearBeforeTestExtension
{
    protected function clear(): void
    {
        /** @var RepositoryInterface $repository */
        $repository = ApplicationTestCase::service(RepositoryInterface::class);
        $repository->clear();
    }
}
