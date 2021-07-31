<?php

namespace App\Shared\Application\Test;

use App\Shared\Domain\Test\TestKernelInterface;
use PHPUnit\Framework\TestCase;

abstract class ApplicationTestCase extends TestCase
{
    private const TEST_CLASS = 'TEST_CLASS';

    public static function bootKernel(): void
    {
        self::createKernel()::boot();
    }

    public static function service(string $class): mixed
    {
        return self::createKernel()::loadService($class);
    }

    public static function createKernel(): TestKernelInterface
    {
        if (!isset($_SERVER[self::TEST_CLASS]) && !isset($_ENV[self::TEST_CLASS])) {
            throw new \LogicException(sprintf('"%s" variable must be defined in $_SERVER or $_ENV', self::TEST_CLASS));
        }

        if (!class_exists($classname = (string) ($_ENV[self::TEST_CLASS] ?? $_SERVER[self::TEST_CLASS]))) {
            throw new \RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded', $classname));
        }

        /** @psalm-suppress MixedMethodCall */
        if (!is_a($kernel = new $classname(), TestKernelInterface::class)) {
            throw new \LogicException(sprintf('Class "%s" must implement "%s"', $classname, TestKernelInterface::class));
        }

        return $kernel;
    }
}
