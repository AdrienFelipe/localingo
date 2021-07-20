<?php

declare(strict_types=1);

namespace App\Localingo\Domain;

class Episode
{
    private string $id;
    private User $user;
    private array $samples;

    public function __construct(string $id, User $user, array $samples)
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

    /**
     * @return Word[]
     */
    public function getSamples(): array
    {
        return $this->samples;
    }
}
