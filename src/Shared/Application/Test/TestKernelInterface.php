<?php

namespace App\Shared\Application\Test;

interface TestKernelInterface
{
    public static function boot(): void;

    public static function loadService(string $class): mixed;
}
