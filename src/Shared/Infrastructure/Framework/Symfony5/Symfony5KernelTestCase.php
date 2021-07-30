<?php

namespace App\Shared\Infrastructure\Framework\Symfony5;

use App\Shared\Application\Test\TestKernelInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Symfony5KernelTestCase extends KernelTestCase implements TestKernelInterface
{
    public static function boot(): void
    {
        self::bootKernel();
    }

    public static function loadService(string $class): ?object
    {
        return self::getContainer()->get($class);
    }
}
