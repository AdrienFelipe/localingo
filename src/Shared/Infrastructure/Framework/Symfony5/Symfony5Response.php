<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Framework\Symfony5;

use App\Shared\Domain\Controller\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Symfony5Response extends AbstractController implements ResponseInterface
{
    public function build(string $template, array $variables = []): Response
    {
        return $this->render($template, $variables);
    }
}
