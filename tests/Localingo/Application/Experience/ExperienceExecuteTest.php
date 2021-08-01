<?php

namespace App\Tests\Localingo\Application\Experience;

use App\Localingo\Application\Experience\ExperienceExecute;
use App\Localingo\Application\Experience\ExperienceGet;
use App\Localingo\Application\Experience\ExperienceSave;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Shared\Application\Test\ApplicationTestCase;
use App\Tests\Localingo\Domain\Episode\EpisodeProvider;
use App\Tests\Localingo\Domain\Exercise\ExerciseProvider;
use LogicException;

class ExperienceExecuteTest extends ApplicationTestCase
{
    private ExperienceExecute $experienceExecute;
    private ExperienceGet $experienceGet;
    private ExperienceFileInterface $experienceFile;
    private ExperienceSave $experienceSave;

    public function setUp(): void
    {
        $this->experienceExecute = self::service(ExperienceExecute::class);
        $this->experienceGet = self::service(ExperienceGet::class);
        $this->experienceSave = self::service(ExperienceSave::class);
        $this->experienceFile = self::service(ExperienceFileInterface::class);
    }

    /**
     * @return array[]
     */
    public function answerProvider(): array
    {
        $noExperience = ['word' => 'none', 'declination' => 'none', 'case' => 'none'];
        $goodExperienceWordOnly = ['word' => 'good', 'declination' => 'none', 'case' => 'none'];
        $goodExperienceAll = ['word' => 'good', 'declination' => 'good', 'case' => 'good'];
        $badExperienceWordOnly = ['word' => 'bad', 'declination' => 'none', 'case' => 'none'];
        $badExperienceAll = ['word' => 'bad', 'declination' => 'bad', 'case' => 'bad'];

        return [
            // New exercises should have no experience added from applying an answer, regardless of the 'correct' value.
            [ExerciseProvider::word(true), 'correct' => true, 'experience' => $noExperience],
            [ExerciseProvider::word(true), 'correct' => false, 'experience' => $noExperience],
            [ExerciseProvider::translation(true), 'correct' => false, 'experience' => $noExperience],
            [ExerciseProvider::translation(true), 'correct' => false, 'experience' => $noExperience],
            [ExerciseProvider::declined(true), 'correct' => false, 'experience' => $noExperience],
            [ExerciseProvider::declined(true), 'correct' => false, 'experience' => $noExperience],

            // Correct 'translation' answers should only add 'good' experience to the word item.
            [ExerciseProvider::word(false), 'correct' => true, 'experience' => $goodExperienceWordOnly],
            [ExerciseProvider::translation(false), 'correct' => true, 'experience' => $goodExperienceWordOnly],
            // Incorrect 'translation' answers should only add 'bad' experience to the word item.
            [ExerciseProvider::word(false), 'correct' => false, 'experience' => $badExperienceWordOnly],
            [ExerciseProvider::translation(false), 'correct' => false, 'experience' => $badExperienceWordOnly],

            // Correct 'declination' answers should add 'good' experience to all items.
            [ExerciseProvider::declined(false), 'correct' => true, 'experience' => $goodExperienceAll],
            // Incorrect 'declination' answers should add 'bad' experience to all items.
            [ExerciseProvider::declined(false), 'correct' => false, 'experience' => $badExperienceAll],
        ];
    }

    /**
     * @dataProvider answerProvider
     * @param array<string, string> $expectedExperiences
     */
    public function testApplyAnswer(Exercise $exercise, bool $isCorrect, array $expectedExperiences): void
    {
        $user = $exercise->getEpisode()->getUser();
        // Extract current experience state.
        $experience = $this->experienceGet->current($user);
        $sample = $exercise->getSample();
        $declinationGood = $experience->declinationItem($sample)->getGood();
        $declinationBad = $experience->declinationItem($sample)->getBad();
        $wordGood = $experience->wordItem($sample)->getGood();
        $wordBad = $experience->wordItem($sample)->getBad();
        $caseGood = $experience->caseItem($sample)->getGood();
        $caseBad = $experience->caseItem($sample)->getBad();

        // Tested method.
        $this->experienceExecute->applyAnswer($exercise, $isCorrect);
        $experience = $this->experienceGet->current($user);

        // Check added experience.
        foreach ($expectedExperiences as $itemType => $experienceType) {
            // Could have used dynamic variables, but this is 'auto-refactorable'.
            if ($itemType === 'declination') {
                $item = $experience->declinationItem($sample);
                $previousGood = $declinationGood;
                $previousBad = $declinationBad;
            } elseif ($itemType === 'word') {
                $item = $experience->wordItem($sample);
                $previousGood = $wordGood;
                $previousBad = $wordBad;
            } elseif ($itemType === 'case') {
                $item = $experience->caseItem($sample);
                $previousGood = $caseGood;
                $previousBad = $caseBad;
            } else {
                throw new LogicException("Item type not mapped: $itemType");
            }

            // Assertions error message.
            $message = "Key: {$this->dataName()} - ItemType: $itemType - Applied: $experienceType - Checking:";

            if ($experienceType === 'none') {
                self::assertSame($previousGood, $item->getGood(), "$message good");
                self::assertSame($previousBad, $item->getBad(), "$message bad");
            } elseif ($experienceType === 'good') {
                self::assertGreaterThan($previousGood, $item->getGood(), "$message good");
                self::assertSame($previousBad, $item->getBad(), "$message bad");
            } elseif ($experienceType === 'bad') {
                self::assertSame($previousGood, $item->getGood(), "$message good");
                self::assertGreaterThan($previousBad, $item->getBad(), "$message bad");
            } else {
                throw new LogicException("Experience type not mapped: $experienceType");
            }
        }
    }

    public function testApplyFinished(): void
    {
        $episode = EpisodeProvider::default();
        $user = $episode->getUser();

        // Add some new data to the experience.
        $experience = $this->experienceGet->current($user);
        $experience->getWordExperiences()->getOrAdd('value');
        $this->experienceSave->toRepository($experience);

        // First, new value should not be persisted to file.
        $experience = $this->experienceFile->read($user);
        self::assertNull($experience);

        // After executing applyFinished, experience should be saved to file.
        $this->experienceExecute->applyFinished($episode);
        $experience = $this->experienceFile->read($user);
        self::assertArrayHasKey('value', $experience->getWordExperiences());
    }
}
