<?php

declare(strict_types=1);

namespace App\Shared\Framework\Symfony;

use App\Shared\Domain\Controller\ResponseInterface;
use App\Shared\Domain\Templating\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SymfonyResponse extends AbstractController implements ResponseInterface
{
    #[\Override]
    public function build(Template $template): Response
    {
        return $this->render($template->filename(), $template->variables());
    }
}
