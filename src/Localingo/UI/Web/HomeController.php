<?php

declare(strict_types=1);

namespace App\Localingo\UI\Web;

use App\Localingo\Application\Episode\EpisodeRender;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Shared\Domain\Controller\ResponseInterface;

class HomeController
{
    private ResponseInterface $response;
    private EpisodeRender $episodeExecute;

    public function __construct(ResponseInterface $response, EpisodeRender $episodeExecute)
    {
        $this->response = $response;
        $this->episodeExecute = $episodeExecute;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function call(): mixed
    {
        $template = $this->episodeExecute->getTemplate();

        return $this->response->build($template);
    }
}
