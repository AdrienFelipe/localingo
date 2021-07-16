<?php

define("SUCCESS_EXIT", 0);
define("FAILURE_EXIT", 1);
define("EXCEPTION_EXIT", 2);

function print_error_message($v) {
    echo "\nCan not find Behat $v classes. Try to update Behat version at Setting|PHP|Behat\n";
}

function find_autoload($behat_dir, $autoload_files)
{
    foreach ($autoload_files as $file) {
        if (file_exists($behat_dir . $file)) {
            return $behat_dir . $file;
        }
    }
    return null;
}

function init($autoload_files)
{
    if (isset($_SERVER['IDE_BEHAT_DIR'])) {
        $behat_exe = $_SERVER['IDE_BEHAT_DIR'];
        if (!file_exists($behat_exe)) {
            echo "The value of behat executable file is specified, but file doesn't exist '$behat_exe'\n";
            exit(FAILURE_EXIT);
        }
        $behat_exe = realpath($behat_exe);
        define('BEHAT_BIN_PATH', $behat_exe);

        $behat_dir = dirname($behat_exe);
        $autoload = find_autoload($behat_dir, $autoload_files);
        if (is_null($autoload)) {
            echo "Can not find behat autoloader file by directory: '$behat_dir'\n";
            exit(FAILURE_EXIT);
        }

        require_once $autoload;
    } else if (isset($_SERVER['IDE_BEHAT_PHAR'])) {
        $behat_phar = $_SERVER['IDE_BEHAT_PHAR'];
        if (!file_exists($behat_phar)) {
            echo "The value \$_SERVER['IDE_BEHAT_PHAR'] is specified, but file doesn't exist '$behat_phar'\n";
            exit(FAILURE_EXIT);
        }

        $behat_phar = realpath($behat_phar);
        define('BEHAT_BIN_PATH', 'phar://' . $behat_phar . '/phar/stub.php');
        require_once 'phar://' . $behat_phar . '/vendor/autoload.php';
    }

    $pathToBinary = defined("PHP_BINARY") ? PHP_BINARY : PHP_BINDIR . DIRECTORY_SEPARATOR . "php";
    define('BEHAT_PHP_BIN_PATH', getenv('PHP_PEAR_PHP_BIN') ?: '/usr/bin/env ' . $pathToBinary);
}

init(array("/../vendor/autoload.php", "/../../../autoload.php"));

$timezone = @date_default_timezone_get();
if (is_null($timezone) || $timezone == "") {
    $timezone = 'UTC';
}
date_default_timezone_set($timezone);

$version = isset($_SERVER['IDE_BEHAT_VERSION']) ? $_SERVER['IDE_BEHAT_VERSION'] : "UNKNOWN";

if (version_compare($version, "3.0") >= 0) {
    if (!interface_exists("\\Behat\\Testwork\\Output\\Formatter")) {
        print_error_message(3);
        return FAILURE_EXIT;
    }

    class PhpStormBehatPrinter {
        private $printer;
        private $verbose;
        private $isFailed = false;

        private $resultConverter;

        public function __construct($printer, $verbose) {
            $this->printer = $printer;
            $this->verbose = $verbose;
            $this->resultConverter = new Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter();
        }

        public function printSummary(array $scenarioStats, array $stepStats, $timer, $memory) {
            $this->writeln("");
            $this->printStats("scenario", $scenarioStats);
            $this->printStats("step", $stepStats);
            $this->writeln(sprintf('%s (%s)', $timer, $memory));
        }

        private function printStats($name, array $stats) {
            $stats = array_filter($stats, function ($count) { return 0 !== $count; });

            if (0 === count($stats)) {
                $totalCount = 0;
            } else {
                $totalCount = array_sum($stats);
            }

            if ($totalCount > 1) {
                $name .= "s";
            }

            $detailedStats = array();
            foreach ($stats as $resultCode => $count) {
                $style = $this->resultConverter->convertResultCodeToString($resultCode);
                $detailedStats[] = sprintf('%d %s', $count, $style);
            }

            $this->write(sprintf('%d %s', $totalCount, $name));
            if (count($detailedStats)) {
                $this->writeln(sprintf(' (%s)', implode(', ', $detailedStats)));
            }
        }

        public function printAfterSetup(Behat\Testwork\EventDispatcher\Event\AfterSetup $event) {
            $setup = $event->getSetup();
            if (!$setup->isSuccessful() && $setup instanceof Behat\Testwork\Hook\Tester\Setup\HookedSetup) {
                $this->printHookException($setup->getHookCallResults());
            }
        }

        public function printAfterTested(Behat\Testwork\EventDispatcher\Event\AfterTested $event) {
            $teardown = $event->getTeardown();
            if (!$teardown->isSuccessful() && $teardown instanceof Behat\Testwork\Hook\Tester\Setup\HookedTeardown) {
                $this->printHookException($teardown->getHookCallResults());
            }
        }

        private function printHookException(\Behat\Testwork\Call\CallResults $result) {
            if ($result->hasExceptions()) {
                foreach ($result->getIterator() as $r) {
                    $exception = $r->getException();
                    if (!is_null($exception)) {
                        $this->printEvent("message", array(
                            "text" => $this->verbose ? (string)$exception : $exception->getMessage() . "\n",
                            "status" => "ERROR"
                        ));
                    }
                }
            }
        }

        public function printSuiteStarted($name, $fileName, $line = '1') {
            $this->printEvent("testSuiteStarted", array(
                "name" => trim($name),
                "locationHint" => "file://$fileName:$line"
            ));
        }

        public function printSuiteFinished($name) {
            $this->printEvent("testSuiteFinished", array("name" => trim($name)));
        }

        public function printStepStarted($testName, $fileName, $line) {
            $this->isFailed = false;
            $this->printEvent("testStarted", array(
                "name" => $testName,
                "captureStandardOutput" => 'true',
                "locationHint" => "file://$fileName:$line"
            ));
        }

        public function printStepFinished($testName, $result, $duration) {
            $param = array(
                "name" => $testName,
                "duration" => $duration
            );
            $this->printStepError($testName, $result, $param);
            $this->printEvent("testFinished", $param);
        }

        private function printStepError($testName, $result, $param) {
            $failType = null;
            switch ($result->getResultCode()) {
                case Behat\Behat\Tester\Result\StepResult::PENDING:
                    $failType = "testIgnored";
                    if ($result instanceof Behat\Behat\Tester\Result\ExecutedStepResult && $result->hasException()) {
                        $exception = $result->getException();
                        if (!is_null($exception)) {
                            $param["message"] = $exception->getMessage();
                        }
                    }
                    else {
                        $param["message"] = "Pending";
                    }
                    break;
                case Behat\Behat\Tester\Result\StepResult::SKIPPED:
                    $failType = "testIgnored";
                    $param["message"] = "Skipped step\n";
                    break;
                case Behat\Behat\Tester\Result\StepResult::UNDEFINED:
                    $failType = "testFailed";
                    if (!$this->isFailed) {
                        $this->isFailed = true;
                        $this->printProgressType($failType);
                    }
                    $param["error"] = 1;
                    $param["message"] = "Undefined step \"$testName\"";
                    break;
                case Behat\Behat\Tester\Result\StepResult::FAILED:
                    $failType = "testFailed";
                    if (!$this->isFailed) {
                        $this->isFailed = true;
                        $this->printProgressType($failType);
                    }

                    if ($result instanceof Behat\Testwork\Tester\Result\ExceptionResult && $result->hasException()) {
                        $exception = $result->getException();
                        $param["message"] = $this->verbose ? (string)$exception : $exception->getMessage();
                    }
                    else {
                        $param["message"] = "Failed";
                    }
                    break;
            }

            if (!is_null($failType)) {
                $this->printEvent($failType, $param);
            }
        }

        public function printProgressType($type)
        {
            $this->printEvent("customProgressStatus", array(
                "type" => $type,
            ));
        }

        public function printProgressStatus($type)
        {
            $this->printEvent("customProgressStatus", array(
                "testsCategory" => $type,
                "count" => '0'
            ));
        }

        public function printEvent($eventName, $params = array())
        {
            $time = self::escapeValue(date("Y-m-d\Th:m:s.000O"));
            $this->write("\n##teamcity[$eventName");
            foreach ($params as $key => $value) {
                $safe_key = self::escapeValue($key);
                $safe_value = self::escapeValue($value);
                $this->write(" $safe_key='$safe_value'");
            }
            $this->write(" timestamp='$time'");
            $this->write("]\n");
        }

        private static function escapeValue($text)
        {
            $text = str_replace("|", "||", $text);
            $text = str_replace("'", "|'", $text);
            $text = str_replace("\n", "|n", $text);
            $text = str_replace("\r", "|r", $text);
            $text = str_replace("]", "|]", $text);
            $text = str_replace("[", "|[", $text);
            return $text;
        }

        private function write($text) {
            $this->printer->write($text);
        }

        private function writeln($text) {
            $this->printer->writeln($text);
        }
    }

    final class PhpStormBehatFormatter implements Behat\Testwork\Output\Formatter
    {
        private $eventPrinter;

        private $printer;
        private $statistics;
        private $listeners;

        private $testStartTime = -1;

        public function __construct()
        {
            $input = new Symfony\Component\Console\Input\ArgvInput();
            $verbose = $input->hasParameterOption(array("-vv", "-vvv"));
            $level = $verbose ?
                Behat\Testwork\Output\Printer\OutputPrinter::VERBOSITY_VERY_VERBOSE :
                Behat\Testwork\Output\Printer\OutputPrinter::VERBOSITY_NORMAL;
            $exceptionPresenter = new Behat\Testwork\Exception\ExceptionPresenter(null, $level);
            $this->printer = $this->createOutputPrinter();
            $this->statistics = $this->createStatisticHandler();
            $this->listeners = array(
                new Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener($this->statistics),
                new Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener($this->statistics, $exceptionPresenter),
            );
            $this->eventPrinter = new PhpStormBehatPrinter($this->printer, $verbose);
        }

        public function createOutputPrinter() {
            if (class_exists("\\Behat\\Behat\\Output\\Printer\\ConsoleOutputPrinter")) {
                return new Behat\Behat\Output\Printer\ConsoleOutputPrinter();
            }
            // for the latest(20.03.15) dev-version ConsoleOutputPrinter class was moved to StreamOutputPrinter
            // https://github.com/Behat/Behat/pull/676/files
            $factory = new Behat\Testwork\Output\Printer\Factory\ConsoleOutputFactory();
            return new  \Behat\Testwork\Output\Printer\StreamOutputPrinter($factory);
        }

        public function createStatisticHandler() {
            if (class_exists("\\Behat\\Behat\\Output\\Statistics\\Statistics")) {
                return new Behat\Behat\Output\Statistics\Statistics();
            }
            // for the latest(20.03.15) dev-version Statistic class was moved to TotalStatistics
            // https://github.com/Behat/Behat/pull/676/files
            return new Behat\Behat\Output\Statistics\TotalStatistics();
        }

        /**
         * {@inheritdoc}
         */
        public static function getSubscribedEvents()
        {
            return array(
                Behat\Testwork\EventDispatcher\Event\SuiteTested::BEFORE => 'beforeSuiteTested',
                Behat\Testwork\EventDispatcher\Event\SuiteTested::AFTER_SETUP => 'afterSuiteSetup',
                Behat\Behat\EventDispatcher\Event\FeatureTested::BEFORE => 'beforeFeatureTested',
                Behat\Behat\EventDispatcher\Event\FeatureTested::AFTER_SETUP => 'afterFeatureSetup',

                Behat\Behat\EventDispatcher\Event\ScenarioTested::BEFORE => 'beforeScenarioTested',
                Behat\Behat\EventDispatcher\Event\ScenarioTested::AFTER_SETUP => 'afterScenarioSetup',
                Behat\Behat\EventDispatcher\Event\StepTested::BEFORE => 'beforeStepTested',
                Behat\Behat\EventDispatcher\Event\StepTested::AFTER_SETUP => 'afterStepSetup',
                Behat\Behat\EventDispatcher\Event\StepTested::AFTER => 'afterStepTested',
                Behat\Behat\EventDispatcher\Event\ScenarioTested::AFTER => 'afterScenarioTested',

                Behat\Behat\EventDispatcher\Event\OutlineTested::BEFORE => 'beforeOutlineTested',
                Behat\Behat\EventDispatcher\Event\OutlineTested::AFTER_SETUP => 'afterOutlineSetup',
                Behat\Behat\EventDispatcher\Event\ExampleTested::BEFORE => 'beforeExampleTested',
                Behat\Behat\EventDispatcher\Event\ExampleTested::AFTER_SETUP => 'afterExampleSetup',
                Behat\Behat\EventDispatcher\Event\ExampleTested::AFTER => 'afterExampleTested',
                Behat\Behat\EventDispatcher\Event\OutlineTested::AFTER => 'afterOutlineTested',

                Behat\Behat\EventDispatcher\Event\FeatureTested::AFTER => 'afterFeatureTested',
                Behat\Testwork\EventDispatcher\Event\SuiteTested::AFTER => 'afterSuiteTested',
            );
        }

        /**
         * {@inheritdoc}
         */
        public function getName()
        {
            return 'PHPStorm formatter';
        }

        /**
         * {@inheritdoc}
         */
        public function getDescription()
        {
            return 'Integrates Behat with PHPStorm';
        }

        /**
         * {@inheritdoc}
         */
        public function getOutputPrinter()
        {
            return $this->printer;
        }

        /**
         * {@inheritdoc}
         */
        public function setParameter($name, $value)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function getParameter($name)
        {
        }

        public function notifyListeners($event) {
            foreach ($this->listeners as $l) {
                $l->listenEvent($this, $event, null);
            }
        }

        public function beforeSuiteTested(Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested $event)
        {
            $this->notifyListeners($event);
            $this->eventPrinter->printEvent("enteredTheMatrix");
            $this->eventPrinter->printProgressStatus("Scenarios");
            $this->statistics->startTimer();
        }

        public function afterSuiteSetup(Behat\Testwork\EventDispatcher\Event\AfterSuiteSetup $event) {
            $this->notifyListeners($event);
            $this->eventPrinter->printAfterSetup($event);
        }

        public function afterSuiteTested(Behat\Testwork\EventDispatcher\Event\AfterSuiteTested $event)
        {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterTested($event);
            $this->eventPrinter->printProgressStatus("");
            $this->statistics->stopTimer();

            $scenarioStat = $this->statistics->getScenarioStatCounts();
            $stepStat = $this->statistics->getStepStatCounts();
            $timer = $this->statistics->getTimer();
            $memory = $this->statistics->getMemory();

            $this->eventPrinter->printSummary($scenarioStat, $stepStat, $timer, $memory);
        }

        public function beforeFeatureTested(Behat\Behat\EventDispatcher\Event\BeforeFeatureTested $event)
        {
            $this->notifyListeners($event);

            $feature = $event->getFeature();
            $fileName = $feature->getFile();
            $this->eventPrinter->printSuiteStarted($this->getFeatureOrScenarioName($feature), $fileName);
        }

        public function afterFeatureSetup(Behat\Behat\EventDispatcher\Event\AfterFeatureSetup $event) {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterSetup($event);
        }

        public function afterFeatureTested(Behat\Behat\EventDispatcher\Event\AfterFeatureTested $event)
        {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterTested($event);
            $this->eventPrinter->printSuiteFinished($this->getFeatureOrScenarioName($event->getFeature()));
        }

        public function beforeScenarioTested(Behat\Behat\EventDispatcher\Event\BeforeScenarioTested $event)
        {
            $this->notifyListeners($event);

            $node = $event->getScenario();
            $name = $this->getFeatureOrScenarioName($node);
            $this->eventPrinter->printSuiteStarted($name, $event->getFeature()->getFile(), $node->getLine());
            $this->eventPrinter->printProgressType("testStarted");
        }

        public function afterScenarioSetup(Behat\Behat\EventDispatcher\Event\AfterScenarioSetup $event) {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterSetup($event);
        }

        public function afterScenarioTested(Behat\Behat\EventDispatcher\Event\AfterScenarioTested $event)
        {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterTested($event);
            $this->eventPrinter->printProgressType("testFinished");
            $this->eventPrinter->printSuiteFinished($this->getFeatureOrScenarioName($event->getScenario()));
        }

        public function beforeOutlineTested(Behat\Behat\EventDispatcher\Event\BeforeOutlineTested $event)
        {
            $this->notifyListeners($event);

            $node = $event->getOutline();
            $name = $this->getFeatureOrScenarioName($node);
            $this->eventPrinter->printSuiteStarted($name, $event->getFeature()->getFile(), $node->getLine());
            foreach ($node->getSteps() as $step) {
                $this->getOutputPrinter()->writeln($this->getStepName($step));
            }
        }

        public function afterOutlineSetup(Behat\Behat\EventDispatcher\Event\AfterOutlineSetup $event) {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterSetup($event);
        }

        public function afterOutlineTested(Behat\Behat\EventDispatcher\Event\AfterOutlineTested $event)
        {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterTested($event);
            $this->eventPrinter->printSuiteFinished($this->getFeatureOrScenarioName($event->getOutline()));
        }

        public function beforeExampleTested(Behat\Behat\EventDispatcher\Event\BeforeScenarioTested $event)
        {
            $this->notifyListeners($event);

            $name = $this->getExampleName($event->getScenario());
            $line = $event->getScenario()->getLine();
            $this->eventPrinter->printSuiteStarted($name, $event->getFeature()->getFile(), $line);
            $this->eventPrinter->printProgressType("testStarted");
        }

        public function afterExampleSetup(Behat\Behat\EventDispatcher\Event\AfterScenarioSetup $event) {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterSetup($event);
        }

        public function afterExampleTested(Behat\Behat\EventDispatcher\Event\AfterScenarioTested $event)
        {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterTested($event);

            $name = $this->getExampleName($event->getScenario());
            $this->eventPrinter->printProgressType("testFinished");
            $this->eventPrinter->printSuiteFinished($name);
        }

        public function beforeStepTested(Behat\Behat\EventDispatcher\Event\BeforeStepTested $event)
        {
            $this->notifyListeners($event);

            $this->testStartTime = microtime(true);
            $step = $event->getStep();
            $testName = $this->getStepName($step);
            $fileName = $event->getFeature()->getFile();
            $line = $step->getLine();
            $this->eventPrinter->printStepStarted($testName, $fileName, $line);
        }

        public function afterStepSetup(Behat\Behat\EventDispatcher\Event\AfterStepSetup $event) {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterSetup($event);
        }

        public function afterStepTested(Behat\Behat\EventDispatcher\Event\AfterStepTested $event)
        {
            $this->notifyListeners($event);

            $this->eventPrinter->printAfterTested($event);
            $testName = $this->getStepName($event->getStep());
            $result = $event->getTestResult();
            $duration = (int)((microtime(true) - $this->testStartTime) * 1000);
            $this->eventPrinter->printStepFinished($testName, $result, $duration);
        }

        private function getFeatureOrScenarioName(Behat\Gherkin\Node\KeywordNodeInterface $node, $haveBaseIndent = true)
        {
            $keyword    = $node->getKeyword();
            $baseIndent = ($node instanceof Behat\Gherkin\Node\FeatureNode) || !$haveBaseIndent ? '' : '  ';

            $lines = explode("\n", $node->getTitle());
            $title = array_shift($lines);

            if (count($lines)) {
                foreach ($lines as $line) {
                    $title .= "\n" . $baseIndent.'  '.$line;
                }
            }

            return "$baseIndent$keyword:" . ($title ? ' ' . $title : '');
        }

        private function getExampleName(Behat\Gherkin\Node\ScenarioInterface $scenario)
        {
            return "Example: " . $scenario->getTitle();
        }

        private function getStepName(Behat\Gherkin\Node\StepNode $step)
        {
            return $step->getType() . " " . $step->getText();
        }
    }

    $factory = new \Behat\Behat\ApplicationFactory();
    $factory->createApplication()->run();
} else {
    if (!class_exists("\\Behat\\Behat\\Formatter\\PrettyFormatter")) {
        print_error_message(2);
        return;
    }

    class PhpStormBehatFormatter extends \Behat\Behat\Formatter\PrettyFormatter
    {
        private $isFailed = false;
        private $testStartTime = -1;

        protected function getDefaultParameters()
        {
            return array();
        }

        public static function getSubscribedEvents()
        {
            $events = array(
                'beforeSuite', 'afterSuite', 'beforeFeature', 'afterFeature', 'beforeScenario',
                'afterScenario', 'beforeBackground', 'afterBackground', 'beforeOutline', 'afterOutline',
                'beforeOutlineExample', 'afterOutlineExample', 'beforeStep', 'afterStep'
            );

            return array_combine($events, $events);
        }

        public function beforeSuite(Behat\Behat\Event\SuiteEvent $event)
        {
            $this->printEvent("enteredTheMatrix");
            $this->printProgressStatus("Scenarios");
        }

        public function afterSuite(Behat\Behat\Event\SuiteEvent $event)
        {
            $this->printProgressStatus("");
            $this->writeln();
            $this->printSummary($event->getLogger());
        }

        public function beforeFeature(Behat\Behat\Event\FeatureEvent $event)
        {
            $feature = $event->getFeature();
            $fileName = $feature->getFile();
            $this->printEvent("testSuiteStarted", array(
                "name" => $this->getFeatureOrScenarioName($feature),
                "locationHint" => "file://$fileName:1"
            ));
        }

        public function afterFeature(Behat\Behat\Event\FeatureEvent $event)
        {
            $this->printEvent("testSuiteFinished",
                array(
                    "name" => $this->getFeatureOrScenarioName($event->getFeature())
                ));
        }

        public function beforeScenario(Behat\Behat\Event\ScenarioEvent $event)
        {
            $this->printStartScenarioEvent($event->getScenario());
            $this->printProgressType("testStarted");
        }

        public function afterScenario(Behat\Behat\Event\ScenarioEvent $event)
        {
            $this->printProgressType("testFinished");
            $this->printEndScenarioEvent($event->getScenario());
        }

        public function beforeOutline(Behat\Behat\Event\OutlineEvent $event)
        {
            $this->printStartScenarioEvent($event->getOutline());
            foreach ($event->getOutline()->getSteps() as $step) {
                $this->writeln($this->getStepName($step));
            }
        }

        public function afterOutline(Behat\Behat\Event\OutlineEvent $event)
        {
            $this->printEndScenarioEvent($event->getOutline());
        }

        public function beforeOutlineExample(Behat\Behat\Event\OutlineExampleEvent $event)
        {
            $outline = $event->getOutline();
            $examples = $outline->getExamples();
            $lines = method_exists($examples, "getRowLines") ? $examples->getRowLines() : array();
            if ($event->getIteration() + 1 < count($lines)) {
                $line = $lines[$event->getIteration() + 1];
            }
            else {
                $line = $outline->getLine();
            }
            $name = $this->getExampleName($event);
            $fileName = $outline->getFile();
            $this->printEvent("testSuiteStarted", array(
                "name" => trim($name),
                "locationHint" => "file://$fileName:$line"
            ));
            $this->printProgressType("testStarted");
        }

        public function afterOutlineExample(Behat\Behat\Event\OutlineExampleEvent $event)
        {
            $this->printProgressType("testFinished");
            $this->printEndScenarioEventByName($event->getOutline(), $this->getExampleName($event));
        }

        public function getExampleName(Behat\Behat\Event\OutlineExampleEvent $event)
        {
            $rowAsString = $event->getOutline()->getExamples()->getRowAsString($event->getIteration() + 1);
            return "Example: " . $rowAsString;
        }

        public function beforeBackground(Behat\Behat\Event\BackgroundEvent $event)
        {
            // don't print background (at least for now)
        }

        public function afterBackground(Behat\Behat\Event\BackgroundEvent $event)
        {
            // don't print background (at least for now)
        }

        public function beforeStep(Behat\Behat\Event\StepEvent $event)
        {
            $this->printStartStepEvent($event);
        }

        public function afterStep(Behat\Behat\Event\StepEvent $event)
        {
            $this->printEndStepEvent($event);
        }

        private function getStepName(Behat\Gherkin\Node\StepNode $step)
        {
            return $step->getType() . " " . $step->getText();
        }

        private function printEvent($eventName, $params = array())
        {
            $this->write("\n##teamcity[$eventName");
            foreach ($params as $key => $value) {
                $safe_key = self::escapeValue($key);
                $safe_value = self::escapeValue($value);
                $this->write(" $safe_key='$safe_value'");
            }
            $time = self::escapeValue(date("Y-m-d\Th:m:s.000O"));
            $this->write(" timestamp='$time'");
            $this->write("]\n");
        }

        private static function escapeValue($text)
        {
            $text = str_replace("|", "||", $text);
            $text = str_replace("'", "|'", $text);
            $text = str_replace("\n", "|n", $text);
            $text = str_replace("\r", "|r", $text);
            $text = str_replace("]", "|]", $text);
            $text = str_replace("[", "|[", $text);
            return $text;
        }

        private function printProgressType($type)
        {
            $this->printEvent("customProgressStatus", array(
                "type" => $type,
            ));
        }

        private function printProgressStatus($type)
        {
            $this->printEvent("customProgressStatus", array(
                "testsCategory" => $type,
                "count" => '0'
            ));
        }

        private function printStartStepEvent(Behat\Behat\Event\StepEvent $event)
        {
            $this->isFailed = false;
            $this->testStartTime = microtime(true);

            $step = $event->getStep();
            $testName = $this->getStepName($step);
            $fileName = $step->getFile();
            $line = $step->getLine();
            $this->printEvent("testStarted", array(
                "name" => $testName,
                "captureStandardOutput" => 'true',
                "locationHint" => "file://$fileName:$line"
            ));
        }

        private function printEndStepEvent(Behat\Behat\Event\StepEvent $event)
        {
            $testName = $this->getStepName($event->getStep());
            $result = $event->getResult();
            $duration = (int)((microtime(true) - $this->testStartTime) * 1000);
            $param = array(
                "name" => $testName,
                "duration" => $duration
            );
            $this->printStepError($event, $result, $param);
            $this->printEvent("testFinished", $param);
        }

        private function printStepError(Behat\Behat\Event\StepEvent $event, $result, $param) {
            $verbose = $this->parameters->get("verbose");
            $failType = null;
            switch ($result) {
                case Behat\Behat\Event\StepEvent::PENDING:
                    $failType = "testIgnored";
                    $exception = $event->getException();
                    if (!is_null($exception)) {
                        $param["message"] = $verbose ? (string)$exception : $exception->getMessage();
                    }
                    break;
                case Behat\Behat\Event\StepEvent::SKIPPED:
                    $failType = "testIgnored";
                    $param["message"] = "Skipped step\n";
                    break;
                case Behat\Behat\Event\StepEvent::UNDEFINED:
                    $param["error"] = 1;
                case Behat\Behat\Event\StepEvent::FAILED:
                    $failType = "testFailed";
                    if (!$this->isFailed) {
                        $this->isFailed = true;
                        $this->printProgressType($failType);
                    }

                    $snippet = $event->getSnippet();
                    if (!is_null($snippet)) {
                        $param["details"] = $this->createSnippet($snippet);
                    }
                    $exception = $event->getException();
                    $param["message"] = $verbose ? (string)$exception : $exception->getMessage();
                    break;
            }

            if (!is_null($failType)) {
                $this->printEvent($failType, $param);
            }
        }

        private function printStartScenarioEvent(Behat\Gherkin\Node\AbstractScenarioNode $node)
        {
            $name = $this->getFeatureOrScenarioName($node);
            $fileName = $node->getFile();
            $line = $node->getLine();
            $this->printEvent("testSuiteStarted", array(
                "name" => trim($name),
                "locationHint" => "file://$fileName:$line"
            ));
        }

        private function printEndScenarioEvent(Behat\Gherkin\Node\AbstractNode $node)
        {
            $this->printEndScenarioEventByName($node, $this->getFeatureOrScenarioName($node));
        }

        private function printEndScenarioEventByName(Behat\Gherkin\Node\AbstractNode $node, $name)
        {
            $this->printEvent("testSuiteFinished",
                array(
                    "name" => trim($name)
                ));
        }

        private function createSnippet($snippet)
        {
            $text = "\n" . $this->translate('proposal_title') . "\n";
            $snippetText = $snippet->getSnippet();
            if ($this->getParameter('snippets_paths')) {
                $indent = str_pad(
                    '', mb_strlen($snippetText) - mb_strlen(ltrim($snippetText)), ' '
                );

                $text .= $indent. "\n";
                foreach ($snippet->getSteps() as $step) {
                    $text .= sprintf(
                            '%s * %s %s # %s:%d', $indent,
                            $step->getType(), $step->getText(),
                            $this->relativizePathsInString($step->getFile()), $step->getLine()
                        ) . "\n";
                }

                if (false !== mb_strpos($snippetText, '/**')) {
                    $snippetText = str_replace('/**', ' *', $snippetText);
                } else {
                    $text .= $indent . "*/\n";
                }
            }
            $text .= $snippetText . "\n";
            return $text;
        }
    }

    $app = new Behat\Behat\Console\BehatApplication($version);
    $app->run();
}
