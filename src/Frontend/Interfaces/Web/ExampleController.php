<?php

declare(strict_types=1);

namespace App\Frontend\Interfaces\Web;

use Symfony\Component\HttpFoundation\Response;

class ExampleController
{
    public function number(int $max): Response
    {
        $number = random_int(0, $max);

        return new Response(
            '<html><body>Example number: '.$number.'</body></html>'
        );
    }
}
