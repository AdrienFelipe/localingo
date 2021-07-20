<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\User\UserGet;
use App\Localingo\Application\Word\WordService;
use App\Localingo\Domain\Entity\Episode;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Store\EpisodeStoreInterface;
use App\Shared\Application\Session\SessionInterface;
use Exception;
use function implode;
use Predis\Client;

class EpisodeService
{
    private const WORDS_BY_EPISODE = 10;
    private const EPISODE_EXPIRE = 604800; // 1 week in seconds.
    public const KEY_EPISODE_ID = 'episode_id';

    private Client $redis;
    private WordService $wordService;
    private SessionInterface $session;
    private UserGet $userGet;
    private EpisodeStoreInterface $episodeStore;

    public function __construct(Client $redis, WordService $wordService, SessionInterface $session, UserGet $userGet, EpisodeStoreInterface $episodeStore)
    {
        $this->redis = $redis;
        $this->wordService = $wordService;
        $this->session = $session;
        $this->userGet = $userGet;
        $this->episodeStore = $episodeStore;
    }

    public function current(): ?Episode
    {
        // Get episode ID from current session or exit if none defined.
        if (!$episodeId = $this->session->get(self::KEY_EPISODE_ID)) {
            return null;
        }

        // Get current session user.
        $user = $this->userGet->current();

        return $user ? $this->episodeStore->load($user, $episodeId) : null;
    }

    public function new(): Episode
    {
        // TODO: Check against id collisions (search for existing ids in a while loop).
        try {
            $id = (string) random_int(1, 10000);
        } catch (Exception $e) {
            $id = '0';
        }

        // Choose word selection.
        $samples = $this->select_samples();
        // Load or create user.
        $user = $this->userGet->current() ?: $this->userGet->new();

        return new Episode($id, $user, $samples);
    }

    public function save(Episode $episode): void
    {
        // Save to store.
        $this->episodeStore->save($episode, self::EPISODE_EXPIRE);
        // Save to session.
        $this->session->set(self::KEY_EPISODE_ID, $episode->getId());
    }

    private function select_samples(): SampleCollection
    {
        // Choose declination.
        $declination = $this->redis->srandmember(WordService::DECLINATION_INDEX);
        // Choose words.
        $words = (array) $this->redis->srandmember(WordService::WORD_INDEX, self::WORDS_BY_EPISODE);
        $key_pattern = WordService::key_pattern(null, $declination);
        $pattern = '/:('.implode('|', $words).'):/';

        $result = ['0'];
        $keys = [];
        do {
            $result = $this->redis->scan($result[0], ['match' => $key_pattern]);
            $values = preg_grep($pattern, $result[1]) ?: [];
            array_push($keys, ...$values);
        } while ('0' !== $result[0]);

        $samples = [];
        foreach ($keys as $key) {
            $samples[] = $this->wordService->getWord($key);
        }

        return new SampleCollection($samples);
    }
}
