<?php

declare(strict_types=1);

namespace App\Shared\Domain\Session;

interface SessionInterface
{
    public function set(string $name, mixed $value): void;

    public function get(string $name, mixed $default = null): mixed;

    public function clear(): void;
}
