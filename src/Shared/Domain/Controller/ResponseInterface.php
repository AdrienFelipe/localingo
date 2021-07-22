<?php

declare(strict_types=1);

namespace App\Shared\Domain\Controller;

use App\Localingo\Domain\Sample\Sample;

interface ResponseInterface
{
    /**
     * @param array<string, mixed> $variables
     */
    public function build(string $template, array $variables, Sample $sample): mixed;
}
