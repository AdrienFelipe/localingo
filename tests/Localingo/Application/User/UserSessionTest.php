<?php

declare(strict_types=1);

namespace App\Tests\Localingo\Application\User;

use App\Localingo\Application\User\UserSession;
use App\Localingo\Domain\User\User;
use App\Shared\Application\Test\ApplicationTestCase;

class UserSessionTest extends ApplicationTestCase
{
    private UserSession $userSession;

    public function setUp(): void
    {
        $this->userSession = self::service(UserSession::class);
    }

    public function testUserId(): void
    {
        // First, user session id should be null.
        $userId = $this->userSession->loadUserId();
        self::assertNull($userId);

        // Build a new user and save its id.
        $user = new User('test-user');
        $this->userSession->saveUserId($user);

        // Saved id should be the same as the user's.
        $userId = $this->userSession->loadUserId();
        self::assertEquals($user->getId(), $userId);
    }
}
