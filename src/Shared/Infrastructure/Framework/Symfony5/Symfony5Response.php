<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Framework\Symfony5;

use App\Shared\Domain\Controller\ResponseInterface;
use App\Shared\Domain\Templating\ValueObject\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Symfony5Response extends AbstractController implements ResponseInterface
{
    public function build(Template $template): Response
    {
        return $this->render($template->filename(), $template->variables());
    }
}
