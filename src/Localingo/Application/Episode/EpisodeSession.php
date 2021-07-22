<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Domain\Episode\Episode;
use App\Shared\Domain\Session\SessionInterface;

class EpisodeSession
{
    private const KEY_EPISODE_ID = 'episode_id';
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function loadEpisodeId(): ?string
    {
        /** @var ?string $episodeId */
        $episodeId = $this->session->get(self::KEY_EPISODE_ID);

        return is_string($episodeId) ? $episodeId : null;
    }

    public function saveEpisodeId(Episode $episode): void
    {
        $this->session->set(self::KEY_EPISODE_ID, $episode->getId());
    }
}
