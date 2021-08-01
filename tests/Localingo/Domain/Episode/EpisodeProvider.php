<?php

namespace App\Tests\Localingo\Domain\Episode;

use App\Localingo\Domain\Episode\Episode;
use App\Tests\Localingo\Domain\User\UserProvider;

class EpisodeProvider
{
    public static function default(): Episode
    {
        $user = UserProvider::default();

        return new Episode('test', $user);
    }
}
