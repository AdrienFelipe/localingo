<?php

namespace App\Tests\Localingo\Application\Word;

use App\Localingo\Application\Word\WordSelect;
use App\Tests\Localingo\Domain\Experience\ExperienceProvider;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class WordSelectTest extends ApplicationTestCase
{
    private WordSelect $wordSelect;

    public function setUp(): void
    {
        $this->wordSelect = self::service(WordSelect::class);
    }

    public function testSelect(): void
    {
        $experience = ExperienceProvider::filled();
        $words = $this->wordSelect->mostRelevant($experience, 10);
        self::assertGreaterThan(1, $words);
        // Bad word should be first item.
        self::assertSame(ExperienceProvider::KEY_WORD_BAD, reset($words));
    }
}
