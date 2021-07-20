<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Session;

use App\Shared\Application\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Symfony5Session extends Session implements SessionInterface
{
    /**
     * Enforce type-hinting.
     */
    public function set(string $name, mixed $value): void
    {
        parent::set($name, $value);
    }

    /**
     * Enforce type-hinting.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return parent::get($name, $default);
    }
}
