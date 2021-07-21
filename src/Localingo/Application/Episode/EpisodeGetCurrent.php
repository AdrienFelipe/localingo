<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\User\UserGet;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeRepositoryInterface;
use App\Localingo\Domain\Episode\EpisodeSessionInterface;

class EpisodeGetCurrent
{
    private EpisodeSessionInterface $episodeSession;
    private UserGet $userGet;
    private EpisodeRepositoryInterface $episodeRepository;

    public function __construct(
        EpisodeSessionInterface $session,
        UserGet $userGet,
        EpisodeRepositoryInterface $episodeRepository,
    ) {
        $this->episodeSession = $session;
        $this->userGet = $userGet;
        $this->episodeRepository = $episodeRepository;
    }

    public function current(): ?Episode
    {
        // Get episode ID from current session.
        $episodeId = $this->episodeSession->loadEpisodeId();
        if (null === $episodeId) {
            return null;
        }

        // Get current session user.
        $user = $this->userGet->current();

        return $user ? $this->episodeRepository->load($user, $episodeId) : null;
    }
}
