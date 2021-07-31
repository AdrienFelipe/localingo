<?php

declare(strict_types=1);

namespace App\Localingo\Application\User;

use App\Localingo\Domain\User\User;
use App\Localingo\Domain\User\UserRepositoryInterface;

class UserCreate
{
    private const DEFAULT_USER_NAME = 'anonymous';
    private UserSession $session;
    private UserRepositoryInterface $repository;

    public function __construct(UserSession $session, UserRepositoryInterface $repository)
    {
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * Creates and persists a new user, and makes it the session's current user.
     * TODO: review this implementation when multi-user is considered.
     */
    public function new(string $name = self::DEFAULT_USER_NAME): User
    {
        $user = new User($name);
        // Persist user.
        $this->repository->save($user);
        // Keep track of the user within its session.
        $this->session->saveUserId($user);

        return $user;
    }
}
