<?php

declare(strict_types=1);

namespace App\Localingo\Application\User;

use App\Localingo\Domain\Entity\User;
use App\Localingo\Domain\Store\UserStoreInterface;
use App\Shared\Application\Session\SessionInterface;

class UserGet
{
    private const KEY_USER_ID = 'user_id';
    private const DEFAULT_USER_NAME = 'anonymous';
    private SessionInterface $session;
    private UserStoreInterface $userStore;

    public function __construct(SessionInterface $session, UserStoreInterface $userStore)
    {
        $this->session = $session;
        $this->userStore = $userStore;
    }

    public function current(): ?User
    {
        $user_id = $this->session->get(self::KEY_USER_ID);
        if (!is_string($user_id)) {
            return null;
        }

        return $this->userStore->load($user_id);
    }

    public function new(string $name = self::DEFAULT_USER_NAME): User
    {
        $user = new User($name);
        // Persist user.
        $this->userStore->save($user);
        // Keep track of the user within its session.
        $this->session->set(self::KEY_USER_ID, $user->getId());

        return $user;
    }
}
