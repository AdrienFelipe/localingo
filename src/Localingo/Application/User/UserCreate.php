<?php

declare(strict_types=1);

namespace App\Localingo\Application\User;

use App\Localingo\Domain\User\User;
use App\Localingo\Domain\User\UserRepositoryInterface;

class UserCreate
{
    private const DEFAULT_USER_NAME = 'anonymous';
    private UserSession $session;
    private UserRepositoryInterface $userStore;

    public function __construct(UserSession $session, UserRepositoryInterface $userStore)
    {
        $this->session = $session;
        $this->userStore = $userStore;
    }

    public function new(string $name = self::DEFAULT_USER_NAME): User
    {
        $user = new User($name);
        // Persist user.
        $this->userStore->save($user);
        // Keep track of the user within its session.
        $this->session->saveEpisodeId($user);

        return $user;
    }
}
