<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Sample\SampleBuildCollection;
use App\Localingo\Application\User\UserGet;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeRepositoryInterface;
use Exception;

class EpisodeCreate
{
    private const WORDS_BY_EPISODE = 10;
    private const EPISODE_EXPIRE = 604800; // 1 week in seconds.

    private EpisodeSession $session;
    private UserGet $userGet;
    private EpisodeRepositoryInterface $repository;
    private SampleBuildCollection $buildSamples;

    public function __construct(
        EpisodeSession $session,
        UserGet $userGet,
        EpisodeRepositoryInterface $episodeRepository,
        SampleBuildCollection $buildSamples,
    ) {
        $this->session = $session;
        $this->userGet = $userGet;
        $this->repository = $episodeRepository;
        $this->buildSamples = $buildSamples;
    }

    public function new(): Episode
    {
        // TODO: Check against id collisions (search for existing ids in a while loop).
        try {
            $id = (string) random_int(1, 10000);
        } catch (Exception) {
            $id = '0';
        }

        // Choose word selection.
        $samples = $this->buildSamples->handle(self::WORDS_BY_EPISODE);
        // Load or create user.
        $user = $this->userGet->current() ?: $this->userGet->new();

        $episode = new Episode($id, $user, $samples);
        // Save to store.
        $this->repository->save($episode, self::EPISODE_EXPIRE);
        // Save to session.
        $this->session->saveEpisodeId($episode);

        return $episode;
    }
}
