<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Session;

use App\Shared\Application\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Symfony5Session extends Session implements SessionInterface
{
}
