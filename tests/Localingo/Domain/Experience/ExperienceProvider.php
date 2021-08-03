<?php

namespace App\Tests\Localingo\Domain\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItem;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Tests\Localingo\Domain\User\UserProvider;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class ExperienceProvider
{
    public const KEY_DECLINATION_GOOD = 'key-declination-good';
    public const KEY_DECLINATION_NONE = 'key-declination-none';
    public const KEY_DECLINATION_BAD = 'key-declination-bad';
    public const KEY_WORD_GOOD = 'key-word-good';
    public const KEY_WORD_NONE = 'key-word-none';
    public const KEY_WORD_BAD = 'key-word-bad';
    public const KEY_CASE_GOOD = 'key-case-good';
    public const KEY_CASE_NONE = 'key-case-none';
    public const KEY_CASE_BAD = 'key-case-bad';

    public static function empty(): Experience
    {
        $user = UserProvider::default();

        return new Experience($user);
    }

    public static function filled(): Experience
    {
        $user = UserProvider::default();
        $experience = new Experience($user);

        // Declinations.
        $experience->getDeclinationExperiences()->getOrAdd(self::KEY_DECLINATION_GOOD)->addGood();
        $experience->getDeclinationExperiences()->getOrAdd(self::KEY_DECLINATION_NONE);
        $experience->getDeclinationExperiences()->getOrAdd(self::KEY_DECLINATION_BAD)->addBad();

        // Words.
        $experience->getWordExperiences()->getOrAdd(self::KEY_WORD_GOOD)->addGood();
        $experience->getWordExperiences()->getOrAdd(self::KEY_WORD_NONE);
        $experience->getWordExperiences()->getOrAdd(self::KEY_WORD_BAD)->addBad();

        // Cases.
        $experience->getCaseExperiences()->getOrAdd(self::KEY_CASE_GOOD)->addGood();
        $experience->getCaseExperiences()->getOrAdd(self::KEY_CASE_NONE);
        $experience->getCaseExperiences()->getOrAdd(self::KEY_CASE_BAD)->addBad();

        return $experience;
    }

    public static function assertEquals(ApplicationTestCase $test, Experience $expectedExperience, Experience $experience, string $message = 'Experience'): void
    {
        // General data.
        $test::assertSame($expectedExperience->getVersion(), $experience->getVersion(), "$message version");
        UserProvider::assertEquals($test, $expectedExperience->getUser(), $experience->getUser());

        // Assert declinations.
        $test::assertEquals($expectedExperience->getDeclinationExperiences(), $experience->getDeclinationExperiences(), "$message declinations");
        // Assert words.
        $test::assertEquals($expectedExperience->getWordExperiences(), $experience->getWordExperiences(), "$message words");
        // Assert cases.
        $test::assertEquals($expectedExperience->getCaseExperiences(), $experience->getCaseExperiences(), "$message cases");
    }

    public static function fromSamples(?SampleCollection $good, ?SampleCollection $bad = null, int $score = null): Experience
    {
        $experience = self::empty();

        if ($good !== null) {
            foreach ($good as $sample) {
                $score !== null or $score = ExperienceItem::INCREASE_GOOD;
                $experience->wordItem($sample)->addGood($score);
                $experience->declinationItem($sample)->addGood($score);
                $experience->caseItem($sample)->addGood($score);
            }
        }
        if ($bad !== null) {
            foreach ($bad as $sample) {
                $score !== null or $score = ExperienceItem::INCREASE_BAD;
                $experience->wordItem($sample)->addBad($score);
                $experience->declinationItem($sample)->addBad($score);
                $experience->caseItem($sample)->addBad($score);
            }
        }

        return $experience;
    }
}
