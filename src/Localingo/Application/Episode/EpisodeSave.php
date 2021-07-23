<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeRepositoryInterface;

class EpisodeSave
{
    private const EPISODE_EXPIRE = 604800; // 1 week in seconds.

    private EpisodeSession $session;
    private EpisodeRepositoryInterface $repository;

    public function __construct(
        EpisodeSession $session,
        EpisodeRepositoryInterface $episodeRepository,
    ) {
        $this->session = $session;
        $this->repository = $episodeRepository;
    }

    public function apply(Episode $episode): Episode
    {
        // Save to store.
        $this->repository->save($episode, self::EPISODE_EXPIRE);
        // Save to session.
        $this->session->saveEpisodeId($episode);

        return $episode;
    }
}
