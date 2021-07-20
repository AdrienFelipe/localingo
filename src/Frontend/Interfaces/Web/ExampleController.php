<?php

declare(strict_types=1);

namespace App\Frontend\Interfaces\Web;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ExampleController extends AbstractController
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function number(int $max): Response
    {
        $word = 'lodówkę';
        $case = 'Accusative';
        $noun = 'lodówka';
        $gender = 'feminine';
        $state = 'plural';

        $handle = fopen('/app/files/declinations.tsv', 'rb');
        if ($handle) {
            $start = microtime(true);

            while (($line = fgets($handle)) !== false) {
                $values = explode("\t", $line);
                if (!isset($header)) {
                    $header = $values;
                    continue;
                }

                $key = "{$values[0]}:{$values[1]}:{$values[2]}";
                $this->redis->hmset($key, array_combine($header, $values));
            }
            echo round(microtime(true) - $start, 3);

            fclose($handle);
        } else {
            dump('File errot');
        }

        return $this->render('base.html.twig', [
            'word' => $word,
            'case' => $case,
            'noun' => $noun,
            'gender' => $gender,
            'state' => $state,
        ]);
    }
}
