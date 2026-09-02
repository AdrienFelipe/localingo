<?php

declare(strict_types=1);

namespace App\Shared\Framework\Symfony;

use App\Shared\Domain\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SymfonySession implements SessionInterface
{
    private SymfonySessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        try {
            $this->session = $requestStack->getSession();
        } catch (SessionNotFoundException) {
            // Tests environment do not have an available session.
            // TODO: configure the session mock as the session service for tests.
            $this->session = new Session(new MockArraySessionStorage());
        }
    }

    /**
     * Enforce type-hinting.
     */
    #[\Override]
    public function set(string $name, mixed $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     * Enforce type-hinting.
     */
    #[\Override]
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->session->get($name, $default);
    }

    #[\Override]
    public function clear(): void
    {
        $this->session->clear();
    }
}
