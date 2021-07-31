<?php

namespace App\Shared\Domain\Test;

interface TestKernelInterface
{
    public static function boot(): void;

    public static function loadService(string $class): mixed;
}
