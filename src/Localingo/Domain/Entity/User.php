<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Entity;

class User
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
