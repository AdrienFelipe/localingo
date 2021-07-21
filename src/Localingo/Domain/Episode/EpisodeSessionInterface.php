<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode;

interface EpisodeSessionInterface
{
    public function loadEpisodeId(): ?string;

    public function saveEpisodeId(Episode $episode): void;
}
