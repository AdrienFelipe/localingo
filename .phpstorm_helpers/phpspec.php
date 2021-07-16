<?php

if (!isset($_SERVER['IDE_PHPSPEC_EXE'])) {
    fwrite(STDERR, "The value of PHPSpec executable is not specified" . PHP_EOL);
    exit(1);
}

$exe = realpath($_SERVER['IDE_PHPSPEC_EXE']);
if (!file_exists($exe)) {
    fwrite(STDERR, "The value of PHPSpec executable is specified, but file doesn't exist '$exe'" . PHP_EOL);
    exit(1);
}

$version = "UNKNOWN";
if (isset($_SERVER['IDE_PHPSPEC_VERSION'])) {
    $version = $_SERVER['IDE_PHPSPEC_VERSION'];
}
else {
    $php = escapeshellarg(defined('PHP_BINARY') ? PHP_BINARY : PHP_BINDIR . DIRECTORY_SEPARATOR . "php");
    $v = preg_split('/ /', exec("$php " . escapeshellarg($exe) . " --version --no-ansi"));
    if (is_array($v) && count($v) > 1) {
        $desc = strtolower($v[count($v) - 2]);
        if ($desc == "version" || $desc == "phpspec") {
            $version = $v[count($v) - 1];
        }
    }
}

define('PHPSPEC_VERSION', $version);

function init($dir, $autoload) {
    foreach ($autoload as $file) {
        if (file_exists($dir . $file)) {
            require $dir . $file;
            return;
        }
    }
}

init(getcwd(), array('/vendor/autoload.php', '/../../autoload.php'));

if (Phar::isValidPharFilename(basename($exe), true)) {
    require_once 'phar://' . $exe . '/vendor/autoload.php';
}
else {
    init(dirname($exe), array('/../vendor/autoload.php', '/../../../autoload.php'));
}

class PhpStormFormatter extends \PhpSpec\Formatter\ConsoleFormatter
{
    public function beforeSuite(\PhpSpec\Event\SuiteEvent $event)
    {
        $this->printEvent("enteredTheMatrix");
    }

    public function afterSuite(\PhpSpec\Event\SuiteEvent $event)
    {
        //TODO: add statistic
    }

    public function beforeExample(\PhpSpec\Event\ExampleEvent $event)
    {
        $testName = $event->getTitle();
        $duration = (int)(round($event->getTime(), 2) * 1000);
        $params = array(
            'name' => $testName,
            "duration" => $duration
        );

        $className = $event->getSpecification()->getClassReflection()->getName();
        $methodName = $event->getExample()->getFunctionReflection()->getName();
        $params['locationHint'] = "phpspec://\\$className::$methodName";
        $this->printEvent('testStarted', $params);
    }

    public function afterExample(\PhpSpec\Event\ExampleEvent $event)
    {

        $testName = $event->getTitle();
        $duration = (int)(round($event->getTime(), 2) * 1000);
        $param = array(
            "name" => $testName,
            "duration" => $duration
        );

        $failType = null;
        switch ($event->getResult()) {
            case \PhpSpec\Event\ExampleEvent::PENDING:
                $failType = "testIgnored";
                $param["message"] = $event->getMessage();
                break;
            case \PhpSpec\Event\ExampleEvent::SKIPPED:
                $failType = "testIgnored";
                $param["message"] = $event->getMessage();
                break;
            case \PhpSpec\Event\ExampleEvent::FAILED:
                $failType = "testFailed";
                $param["message"] = $event->getMessage();
                break;
            case \PhpSpec\Event\ExampleEvent::BROKEN:
                $failType = "testFailed";
                $param["error"] = 1;
                $param["message"] = $event->getMessage();
                //TODO: when this one appear
                break;
        }

        if (!is_null($failType)) {
            $this->printEvent($failType, $param);
        }

        $className = $event->getSpecification()->getClassReflection()->getName();
        $methodName = $event->getExample()->getFunctionReflection()->getName();
        $this->printEvent('testFinished', array(
            'name' => $testName,
            'duration' => $duration,
            'locationHint' => "phpspec://\\$className::$methodName"
        ));
    }

    public function beforeSpecification(\PhpSpec\Event\SpecificationEvent $event)
    {
        $suiteName = $event->getTitle();
        if (empty($suiteName)) {
            return;
        }
        $params = array(
            'name' => $suiteName,
        );
        $className = $event->getSpecification()->getClassReflection()->getName();
        $params['locationHint'] = "phpspec://\\$className";
        $this->printEvent('testSuiteStarted', $params);
    }

    public function afterSpecification(\PhpSpec\Event\SpecificationEvent $event)
    {
        $suiteName = $event->getTitle();
        if (empty($suiteName)) {
            return;
        }

        $params = array(
            'name' => $suiteName,
        );
        $className = $event->getSpecification()->getClassReflection()->getName();
        $params['locationHint'] = "phpspec://\\$className";
        $this->printEvent('testSuiteFinished', $params);
    }

    private function printEvent($eventName, $params = array())
    {
        $this->getIO()->write("\n##teamcity[$eventName");
        foreach ($params as $key => $value) {
            $escapedValue = self::escapeValue($value);
            $this->getIO()->write(" $key='$escapedValue'");
        }
        $this->getIO()->write("]\n");
    }

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
}

$app = new \PhpSpec\Console\Application(PHPSPEC_VERSION);
$container = $app->getContainer();

if (version_compare(PHPSPEC_VERSION, "3") >= 0) {
    $container->define(
        'formatter.formatters.phpstorm',
        function (\PhpSpec\ServiceContainer\IndexedServiceContainer $c) {
            return new PhpStormFormatter(
                $c->get('formatter.presenter'),
                $c->get('console.io'),
                $c->get('event_dispatcher.listeners.stats')
            );
        }
    );
}
else {
    $container->set(
        'formatter.formatters.phpstorm',
        function (\PhpSpec\ServiceContainer $c) {
            return new PhpStormFormatter(
                $c->get('formatter.presenter'),
                $c->get('console.io'),
                $c->get('event_dispatcher.listeners.stats')
            );
        }
    );
}

$app->run();
