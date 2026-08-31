<?php

namespace App\Tests\Shared\Infrastructure\Phpunit;

use App\Shared\Domain\File\FileInterface;

class FileTestHook extends ClearBeforeTestExtension
{
    protected function clear(): void
    {
        /** @var FileInterface $repository */
        $repository = ApplicationTestCase::service(FileInterface::class);
        $repository->clear();
    }
}
