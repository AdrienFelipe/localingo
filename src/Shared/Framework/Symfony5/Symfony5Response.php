<?php

declare(strict_types=1);

namespace App\Shared\Framework\Symfony5;

use App\Shared\Domain\Controller\ResponseInterface;
use App\Shared\Domain\Templating\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Symfony5Response extends AbstractController implements ResponseInterface
{
    public function build(Template $template): Response
    {
        return $this->render($template->filename(), $template->variables());
    }
}
