<?php

namespace App\Tests\Localingo\Application\User;

use App\Localingo\Application\User\UserCreate;
use App\Localingo\Application\User\UserGet;
use App\Localingo\Domain\User\User;
use App\Shared\Application\Test\ApplicationTestCase;

class UserGetTest extends ApplicationTestCase
{
    private UserGet $userGet;
    private UserCreate $userCreate;

    public function setUp(): void
    {
        $this->userGet = self::service(UserGet::class);
        $this->userCreate = self::service(UserCreate::class);
    }

    public function testEmptyUser(): void
    {
        self::assertNull($this->userGet->current());
    }

    public function testLoadUser(): void
    {
        // Create and save a new user, should be considered as the current user.
        $expectedUser = $this->userCreate->new('my-test-user');
        // Now load the user.
        $user = $this->userGet->current();

        // Compare user fields.
        self::assertEquals($expectedUser->getId(), $user->getId(), 'User "id" does not match');
        self::assertEquals($expectedUser->getName(), $user->getName(), 'User "name" does not match');
    }
}
