<?php

namespace App\Tests\Localingo\Application\User;

use App\Localingo\Application\User\UserCreate;
use App\Localingo\Application\User\UserSession;
use App\Localingo\Domain\User\User;
use App\Localingo\Domain\User\UserRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;

class UserCreateTest extends ApplicationTestCase
{
    private UserCreate $userCreate;
    private UserRepositoryInterface $userRepository;
    private UserSession $session;

    public function setUp(): void
    {
        $this->userCreate = self::service(UserCreate::class);
        $this->userRepository = self::service(UserRepositoryInterface::class);
        $this->session = self::service(UserSession::class);
    }

    public function testCreateUser(): void
    {
        // Create and save a new user, should be considered as the current user.
        $expectedUser = $this->userCreate->new();

        // Test user was persisted to repository.
        $user = $this->userRepository->load($expectedUser->getId());
        self::assertEquals($expectedUser->getId(), $user->getId(), 'Repository "user id" error');
        self::assertEquals($expectedUser->getName(), $user->getName(), 'Repository "user name" error');

        // Test user id was saved to session.
        $userId = $this->session->loadUserId();
        self::assertEquals($expectedUser->getId(), $userId, 'Session "user id" error');
    }
}
