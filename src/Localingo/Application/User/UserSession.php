<?php

declare(strict_types=1);

namespace App\Localingo\Application\User;

use App\Localingo\Domain\User\User;
use App\Shared\Domain\Session\SessionInterface;

class UserSession
{
    private const KEY_USER_ID = 'user_id';
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function loadUserId(): ?string
    {
        /** @var ?string $userId */
        $userId = $this->session->get(self::KEY_USER_ID);

        return is_string($userId) ? $userId : null;
    }

    public function saveUserId(User $user): void
    {
        $this->session->set(self::KEY_USER_ID, $user->getId());
    }
}
