<?php

declare(strict_types=1);

namespace App\Shared\Domain\Controller;

use App\Shared\Domain\Templating\Template;

interface ResponseInterface
{
    public function build(Template $template): mixed;
}
