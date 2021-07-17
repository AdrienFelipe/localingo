<?php

declare(strict_types=1);

namespace App\Frontend\Interfaces\Web;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


class ExampleController extends AbstractController
{
    private Client $redis;

    public function __construct(Client $redis) {
        $this->redis = $redis;
    }

    public function number(int $max): Response
    {
        $word = 'lodówkę';
        $case = 'Accusative';
        $noun = 'lodówka';
        $gender = 'feminine';
        $state = 'plural';

        return $this->render('base.html.twig', [
            'word' => $word,
            'case' => $case,
            'noun' => $noun,
            'gender' => $gender,
            'state' => $state,
        ]);
    }
}
