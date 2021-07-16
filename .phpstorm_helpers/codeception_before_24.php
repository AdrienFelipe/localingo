<?php

class PhpStorm_Codeception_ReportPrinter extends PHPUnit_TextUI_ResultPrinter
{
    protected $testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;

    protected $failures = [];

    /**
     * @var bool
     */
    private $isSummaryTestCountPrinted = false;

    /**
     * @var string
     */
    private $startedTestName;

    /**
     * @var string
     */
    private $flowId;

    /**
     * @param string $progress
     */
    protected function writeProgress($progress)
    {
    }

    /**
     * @param PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->printHeader();
        $this->printFooter($result);
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addFail(\PHPUnit_Runner_BaseTestRunner::STATUS_ERROR, $test, $e);
    }

    /**
     * A warning occurred.
     *
     * @param PHPUnit_Framework_Test    $test
     * @param PHPUnit_Framework_Warning $e
     * @param float                     $time
     *
     * @since Method available since Release 5.1.0
     */
    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        $this->addFail(\PHPUnit_Runner_BaseTestRunner::STATUS_ERROR, $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $parameters = [];
        if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
            $comparisonFailure = $e->getComparisonFailure();

            if ($comparisonFailure instanceof \SebastianBergmann\Comparator\ComparisonFailure) {
                $expectedString = $comparisonFailure->getExpectedAsString();

                if (is_null($expectedString) || empty($expectedString)) {
                    $expectedString = self::getPrimitiveValueAsString($comparisonFailure->getExpected());
                }

                $actualString = $comparisonFailure->getActualAsString();

                if (is_null($actualString) || empty($actualString)) {
                    $actualString = self::getPrimitiveValueAsString($comparisonFailure->getActual());
                }

                if (!is_null($actualString) && !is_null($expectedString)) {
                    $parameters['type']     = 'comparisonFailure';
                    $parameters['actual']   = $actualString;
                    $parameters['expected'] = $expectedString;
                }
            }
        }

        $this->addFail(\PHPUnit_Runner_BaseTestRunner::STATUS_ERROR, $test, $e, $parameters);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addIgnoredTest($test, $e);
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addError($test, $e, $time);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $testName = self::getTestAsString($test);
        if ($this->startedTestName != $testName) {
            $this->startTest($test);
            $this->printEvent(
                'testIgnored',
                [
                    'name'    => $testName,
                    'message' => self::getMessage($e),
                    'details' => self::getDetails($e),
                ]
            );
            $this->endTest($test, $time);
        } else {
            $this->addIgnoredTest($test, $e);
        }
    }

    public function addIgnoredTest(PHPUnit_Framework_Test $test, Exception $e) {
        $this->addFail(\PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED, $test, $e);
    }

    private function addFail($status, PHPUnit_Framework_Test $test, $e, $parameters = []) {
        $key = self::getTestSignature($test);
        $this->testStatus = $status;
        $parameters['message'] = self::getMessage($e);
        $parameters['details'] = self::getDetails($e);

        $this->failures[$key][] = $parameters;
    }

    /**
     * A testsuite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (stripos(ini_get('disable_functions'), 'getmypid') === false) {
            $this->flowId = getmypid();
        } else {
            $this->flowId = false;
        }

        if (!$this->isSummaryTestCountPrinted) {
            $this->isSummaryTestCountPrinted = true;

            $this->printEvent(
                'testCount',
                ['count' => count($suite)]
            );
        }

        $suiteName = $suite->getName();

        if (empty($suiteName)) {
            return;
        }

        //TODO: configure 'locationHint' to navigate to 'unit', 'acceptance', 'functional' test suite
        //TODO: configure 'locationHint' to navigate to  DataProvider tests for Codeception earlier 2.2.6
        $parameters = ['name' => $suiteName];
        $this->printEvent('testSuiteStarted', $parameters);
    }

    /**
     * A testsuite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $suiteName = $suite->getName();

        if (empty($suiteName)) {
            return;
        }

        $parameters = ['name' => $suiteName];
        $this->printEvent('testSuiteFinished', $parameters);
    }

    public static function getTestSignature(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Codeception\Test\Interfaces\Descriptive) {
            return $testCase->getSignature();
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            return get_class($testCase) . ':' . $testCase->getName(false);
        }
        return $testCase->toString();
    }

    public static function getTestAsString(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Codeception\Test\Interfaces\Descriptive) {
            return $testCase->toString();
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            $text = $testCase->getName();
            $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
            $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
            $text = preg_replace('/^test /', '', $text);
            $text = ucfirst(strtolower($text));
            $text = str_replace(['::', 'with data set'], [':', '|'], $text);
            return Codeception\Util\ReflectionHelper::getClassShortName($testCase) . ': ' . $text;
        }
        return $testCase->toString();
    }

    public static function getTestFileName(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Codeception\Test\Interfaces\Descriptive) {
            return $testCase->getFileName();
        }
        return (new \ReflectionClass($testCase))->getFileName();
    }

    public static function getTestFullName(\PHPUnit_Framework_SelfDescribing $testCase)
    {
        if ($testCase instanceof Codeception\Test\Interfaces\Plain) {
            return self::getTestFileName($testCase);
        }
        if ($testCase instanceof Codeception\Test\Interfaces\Descriptive) {
            $signature = $testCase->getSignature(); // cut everything before ":" from signature
            return self::getTestFileName($testCase) . '::' . preg_replace('~^(.*?):~', '', $signature);
        }
        if ($testCase instanceof \PHPUnit_Framework_TestCase) {
            return self::getTestFileName($testCase) . '::' . $testCase->getName(false);
        }
        return self::getTestFileName($testCase) . '::' . $testCase->toString();
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $testName              = self::getTestAsString($test);
        $this->startedTestName = $testName;
        $location              = "php_qn://" . self::getTestFullName($test);
        $gherkin = self::getGherkinTestLocation($test);
        if ($gherkin != null) {
            $location = $gherkin;
        }
        $params                = ['name' => $testName, 'locationHint' => $location];

        if ($test instanceof \Codeception\Test\Interfaces\ScenarioDriven) {
            $this->printEvent('testSuiteStarted', $params);
        }
        else {
            $this->printEvent('testStarted', $params);
        }
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $result = null;
        switch ($this->testStatus) {
            case \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR:
            case \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE:
                $result = 'testFailed';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED:
                $result = 'testIgnored';
                break;
        }

        $name = self::getTestAsString($test);
        if ($this->startedTestName != $name) {
            $name = $this->startedTestName;
        }
        $gherkin = self::getGherkinTestLocation($test);
        $duration = (int)(round($time, 2) * 1000);
        if ($test instanceof \Codeception\Test\Interfaces\ScenarioDriven) {
            $steps = $test->getScenario()->getSteps();
            $len = sizeof($steps);
            $printed = 0;
            for ($i = 0; $i < $len; $i++) {
                $step = $steps[$i];
                if ($step->getAction() == null && $step->getMetaStep()) {
                    $step = $step->getMetaStep();
                }

                if ($step instanceof \Codeception\Step\Comment) {
                    // TODO: render comments in grey color?
                    // comments are not shown because at the moment it's hard to distinguish them from real tests.
                    // e.g. comment steps show descriptions from *.feature tests.
                    continue;
                }
                $printed++;
                $testName = sprintf('%s %s %s',
                    ucfirst($step->getPrefix()),
                    $step->getHumanizedActionWithoutArguments(),
                    $step->getHumanizedArguments()
                );

                $location = $gherkin != null ? $gherkin : $step->getLine();
                $this->printEvent('testStarted',
                    [
                        'name' => $testName,
                        'locationHint' => "file://$location"
                    ]);

                $params = ['name' => $testName];
                if ($i == $len - 1) {
                    parent::endTest($test, $time);
                    $this->printError($test, $result, $testName);
                    $params['duration'] = $duration;
                }
                $this->printEvent('testFinished', $params);
            }

            if ($printed == 0 && $result != null) {
                $this->printEvent('testStarted', ['name' => $name]);
                parent::endTest($test, $time);
                $this->printError($test, $result, $name);
                $this->printEvent('testFinished', [
                    'name' => $name,
                    'duration' => $duration
                ]);
            }

            $this->printEvent('testSuiteFinished', ['name' => $name]);
        }
        else {
            parent::endTest($test, $time);
            $this->printError($test, $result, self::getTestAsString($test));

            $this->printEvent(
                'testFinished',
                [
                    'name' => self::getTestAsString($test),
                    'duration' => $duration
                ]
            );
        }
    }

    private function printError(PHPUnit_Framework_Test $test, $result, $name) {
        if ($result != null) {
            $this->testStatus = \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
            $key = self::getTestSignature($test);
            if (isset($this->failures[$key])) {
                $failures = $this->failures[$key];
                //TODO: check if it's possible to have sizeof($params) > 1
                assert(sizeof($failures) == 1);

                $params = $failures[0];
                $params['name'] = $name;
                $this->printEvent($result, $params);
                unset($this->failures[$key]);
            }
        }
    }

    /**
     * @param string $eventName
     * @param array  $params
     */
    private function printEvent($eventName, $params = [])
    {
        $this->write("\n##teamcity[$eventName");

        if ($this->flowId) {
            $params['flowId'] = $this->flowId;
        }

        foreach ($params as $key => $value) {
            $escapedValue = self::escapeValue($value);
            $this->write(" $key='$escapedValue'");
        }

        $this->write("]\n");
    }

    private static function getGherkinTestLocation(PHPUnit_Framework_Test $test) {
        if ($test instanceof \Codeception\Test\Gherkin) {
            $feature = $test->getFeatureNode();
            $scenario = $test->getScenarioNode();
            if ($feature != null && $scenario != null) {
                return "file://" . $test->getFeatureNode()->getFile() . ":" . $test->getScenarioNode()->getLine();
            }
        }
        return null;
    }

    /**
     * @param Exception $e
     *
     * @return string
     */
    private static function getMessage(Exception $e)
    {
        $message = '';

        if (!$e instanceof PHPUnit_Framework_Exception) {
            if (strlen(get_class($e)) != 0) {
                $message = $message . get_class($e);
            }

            if (strlen($message) != 0 && strlen($e->getMessage()) != 0) {
                $message = $message . ' : ';
            }
        }

        return $message . $e->getMessage();
    }

    /**
     * @param Exception $e
     *
     * @return string
     */
    private static function getDetails(Exception $e)
    {
        $stackTrace = PHPUnit_Util_Filter::getFilteredStacktrace($e);
        $previous   = $e->getPrevious();

        while ($previous) {
            $stackTrace .= "\nCaused by\n" .
                PHPUnit_Framework_TestFailure::exceptionToString($previous) . "\n" .
                PHPUnit_Util_Filter::getFilteredStacktrace($previous);

            $previous = $previous->getPrevious();
        }

        return ' ' . str_replace("\n", "\n ", $stackTrace);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private static function getPrimitiveValueAsString($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value == true ? 'true' : 'false';
        } elseif (is_scalar($value)) {
            return print_r($value, true);
        }

        return;
    }

    /**
     * @param  $text
     *
     * @return string
     */
    private static function escapeValue($text)
    {
        $text = str_replace('|', '||', $text);
        $text = str_replace("'", "|'", $text);
        $text = str_replace("\n", '|n', $text);
        $text = str_replace("\r", '|r', $text);
        $text = str_replace(']', '|]', $text);
        $text = str_replace('[', '|[', $text);

        return $text;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private static function getFileName($className)
    {
        $reflectionClass = new ReflectionClass($className);
        $fileName        = $reflectionClass->getFileName();

        return $fileName;
    }
}