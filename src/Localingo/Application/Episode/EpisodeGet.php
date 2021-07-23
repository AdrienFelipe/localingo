<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\User\UserGetCurrent;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeRepositoryInterface;

class EpisodeGet
{
    private EpisodeSession $episodeSession;
    private UserGetCurrent $userGetCurrent;
    private EpisodeRepositoryInterface $episodeRepository;

    public function __construct(
        EpisodeSession $session,
        UserGetCurrent $userGetCurrent,
        EpisodeRepositoryInterface $episodeRepository,
    ) {
        $this->episodeSession = $session;
        $this->userGetCurrent = $userGetCurrent;
        $this->episodeRepository = $episodeRepository;
    }

    public function current(): ?Episode
    {
        // Get episode ID from current session.
        $episodeId = $this->episodeSession->loadEpisodeId();
        if ($episodeId === null) {
            return null;
        }

        // Get current session user.
        $user = ($this->userGetCurrent)();

        return $user ? $this->episodeRepository->load($user, $episodeId) : null;
    }
}
