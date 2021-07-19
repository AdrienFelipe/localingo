<?php

declare(strict_types=1);

namespace App\Shared\Application\Session;

interface SessionInterface
{
    public function set(string $name, $value);

    public function get(string $name, $default = null);
}
