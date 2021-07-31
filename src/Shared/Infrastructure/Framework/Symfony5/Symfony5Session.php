<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Framework\Symfony5;

use App\Shared\Domain\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySession;

class Symfony5Session implements SessionInterface
{
    private SymfonySession $session;

    public function __construct(SymfonySession $session)
    {
        $this->session = $session;
    }

    /**
     * Enforce type-hinting.
     */
    public function set(string $name, mixed $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     * Enforce type-hinting.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->session->get($name, $default);
    }

    public function clear(): void
    {
        $this->session->clear();
    }
}
