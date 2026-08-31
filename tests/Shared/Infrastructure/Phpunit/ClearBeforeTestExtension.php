<?php

namespace App\Tests\Shared\Infrastructure\Phpunit;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Resets one infrastructure service before every test.
 */
abstract class ClearBeforeTestExtension implements Extension, PreparedSubscriber
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber($this);
    }

    public function notify(Prepared $event): void
    {
        $this->clear();
    }

    abstract protected function clear(): void;
}
