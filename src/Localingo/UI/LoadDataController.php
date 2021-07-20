<?php

declare(strict_types=1);

namespace App\Localingo\UI;

use App\Localingo\Application\Episode\EpisodeService;
use App\Localingo\Application\Word\WordService;
use App\Shared\Application\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LoadDataController extends AbstractController
{
    private SessionInterface $session;
    private WordService $wordService;
    private EpisodeService $episodeInitialize;

    public function __construct(SessionInterface $session, WordService $wordService, EpisodeService $courseService)
    {
        $this->session = $session;
        $this->wordService = $wordService;
        $this->episodeInitialize = $courseService;
    }

    public function call(): Response
    {
        // Load data to memory.
        $this->wordService->initialize();

        // Load current episode or create a new one.
        $episode = $this->episodeInitialize->current() ?: $this->episodeInitialize->new();
        $this->episodeInitialize->save($episode);

        $word = $episode->getSamples()[0];

        return $this->render('base.html.twig', [
            'word' => $word,
        ]);
    }
}
