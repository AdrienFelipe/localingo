<?php

declare(strict_types=1);

namespace App\Localingo\Application\User;

use App\Localingo\Domain\User\User;
use App\Localingo\Domain\User\UserRepositoryInterface;

class UserGet
{
    private UserSession $session;
    private UserRepositoryInterface $repository;

    public function __construct(UserSession $session, UserRepositoryInterface $userStore)
    {
        $this->session = $session;
        $this->repository = $userStore;
    }

    public function current(): ?User
    {
        $user_id = $this->session->loadUserId();
        if ($user_id === null) {
            return null;
        }

        return $this->repository->load($user_id);
    }
}
