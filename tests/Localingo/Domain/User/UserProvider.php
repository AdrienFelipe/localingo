<?php

namespace App\Tests\Localingo\Domain\User;

use App\Localingo\Domain\User\User;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class UserProvider
{
    public static function default(string $name = 'test'): User
    {
        return new User($name);
    }

    public static function assertEquals(ApplicationTestCase $test, User $expectedUser, User $user, string $message = 'User'): void
    {
        $test::assertEquals($expectedUser->getId(), $user->getId(), "$message id");
        $test::assertEquals($expectedUser->getName(), $user->getName(), "$message name");
    }
}
