<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode;

use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\User\User;

class Episode
{
    private string $id;
    private User $user;
    private SampleCollection $samples;

    public function __construct(string $id, User $user, SampleCollection $samples)
    {
        $this->id = $id;
        $this->user = $user;
        $this->samples = $samples;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSamples(): SampleCollection
    {
        return $this->samples;
    }
}
