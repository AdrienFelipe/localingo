<?php

namespace App\Tests\Shared\Infrastructure\Phpunit;

use App\Shared\Domain\File\FileInterface;
use PHPUnit\Runner\BeforeTestHook;

class FileTestHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        /** @var FileInterface $repository */
        $repository = ApplicationTestCase::service(FileInterface::class);
        $repository->clear();
    }
}
