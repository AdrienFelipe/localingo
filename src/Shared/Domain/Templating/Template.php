<?php

declare(strict_types=1);

namespace App\Shared\Domain\Templating;

class Template
{
    private string $filename;
    /** @var array<string, mixed> */
    private array $variables;

    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(string $filename, array $variables = [])
    {
        $this->filename = $filename;
        $this->variables = $variables;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return array<string, mixed>
     */
    public function variables(): array
    {
        return $this->variables;
    }
}
