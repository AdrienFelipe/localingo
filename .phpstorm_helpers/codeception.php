<?php

if (!isset($_SERVER['IDE_CODECEPTION_EXE'])) {
    fwrite(STDERR, "The value of Codeception executable is not specified" . PHP_EOL);
    exit(1);
}

if (!isset($_SERVER['IDE_SCRIPT_PREFIX'])) {
    fwrite(STDERR, "The value of Codeception helper script prefix is not specified" . PHP_EOL);
    exit(1);
}

$exe = realpath($_SERVER['IDE_CODECEPTION_EXE']);
if (!file_exists($exe)) {
    $originalPath = $_SERVER['IDE_CODECEPTION_EXE'];
    fwrite(STDERR, "The value of Codeception executable is specified, but file doesn't exist '$originalPath'" . PHP_EOL);
    exit(1);
}

if (Phar::isValidPharFilename(basename($exe), true)) {
    $oldAutoloadPath = 'phar://' . $exe . '/autoload.php';
    if (file_exists($oldAutoloadPath)) {
        require_once $oldAutoloadPath;
    }
    else {
        require_once 'phar://' . $exe . '/vendor/codeception/codeception/autoload.php';
    }
}
else {
    require_once dirname($exe) .'/autoload.php';
}
$app = new \Codeception\Application('Codeception', \Codeception\Codecept::VERSION);

if (version_compare(\Codeception\Codecept::VERSION, "2.4.0") < 0) {
    require_once $_SERVER['IDE_SCRIPT_PREFIX'] . 'codeception_before_24.php';
} else {
    if (version_compare(PHPUnit\Runner\Version::id(), "7.0.0") >= 0 && version_compare(phpversion(), "7.0.0") >= 0) {
        if (version_compare(PHPUnit\Runner\Version::id(), "9.0.0") >= 0) {
            require_once $_SERVER['IDE_SCRIPT_PREFIX'] . 'codeception_24_90.php';
        }
        else {
            require_once $_SERVER['IDE_SCRIPT_PREFIX'] . 'codeception_24_70.php';
        }
    } else {
        require_once $_SERVER['IDE_SCRIPT_PREFIX'] . 'codeception_24_56.php';
    }
}

if (version_compare(\Codeception\Codecept::VERSION, "2.2.6") >= 0) {
    $app->add(new \Codeception\Command\Run('run'));
    $app->run();
}
else {
    class PhpStorm_Codeception_Command_Run extends \Codeception\Command\Run {

        public function execute(\Symfony\Component\Console\Input\InputInterface $input,
                                \Symfony\Component\Console\Output\OutputInterface $output)
        {
            $this->ensureCurlIsAvailable();
            $this->options = $input->getOptions();
            $this->output = $output;

            $config = \Codeception\Configuration::config($this->options['config']);

            if (!$this->options['colors']) {
                $this->options['colors'] = $config['settings']['colors'];
            }
            if (!$this->options['silent']) {
                $this->output->writeln(
                    \Codeception\Codecept::versionString() . "\nPowered by " . \PHPUnit_Runner_Version::getVersionString()
                );
            }
            if ($this->options['debug']) {
                $this->output->setVerbosity(\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE);
            }

            $userOptions = array_intersect_key($this->options, array_flip($this->passedOptionKeys($input)));
            $userOptions = array_merge(
                $userOptions,
                $this->booleanOptions($input, ['xml', 'html', 'json', 'tap', 'coverage', 'coverage-xml', 'coverage-html'])
            );
            $userOptions['verbosity'] = $this->output->getVerbosity();
            $userOptions['interactive'] = !$input->hasParameterOption(['--no-interaction', '-n']);
            $userOptions['ansi'] = (!$input->hasParameterOption('--no-ansi') xor $input->hasParameterOption('ansi'));

            if ($this->options['no-colors'] || !$userOptions['ansi']) {
                $userOptions['colors'] = false;
            }
            if ($this->options['group']) {
                $userOptions['groups'] = $this->options['group'];
            }
            if ($this->options['skip-group']) {
                $userOptions['excludeGroups'] = $this->options['skip-group'];
            }
            if ($this->options['report']) {
                $userOptions['silent'] = true;
            }
            if ($this->options['coverage-xml'] or $this->options['coverage-html'] or $this->options['coverage-text']) {
                $this->options['coverage'] = true;
            }
            if (!$userOptions['ansi'] && $input->getOption('colors')) {
                $userOptions['colors'] = true; // turn on colors even in non-ansi mode if strictly passed
            }

            $suite = $input->getArgument('suite');
            $test = $input->getArgument('test');

            if (! \Codeception\Configuration::isEmpty() && ! $test && strpos($suite, $config['paths']['tests']) === 0) {
                list(, $suite, $test) = $this->matchTestFromFilename($suite, $config['paths']['tests']);
            }

            if ($this->options['group']) {
                $this->output->writeln(sprintf("[Groups] <info>%s</info> ", implode(', ', $this->options['group'])));
            }
            if ($input->getArgument('test')) {
                $this->options['steps'] = true;
            }

            if ($test) {
                $filter = $this->matchFilteredTestName($test);
                $userOptions['filter'] = $filter;
            }

            $this->codecept = new PhpStorm_Codeception_Codecept($userOptions);

            if ($suite and $test) {
                $this->codecept->run($suite, $test);
            }

            if (!$test) {
                $suites = $suite ? explode(',', $suite) : \Codeception\Configuration::suites();
                $this->executed = $this->runSuites($suites, $this->options['skip']);

                if (!empty($config['include']) and !$suite) {
                    $current_dir = \Codeception\Configuration::projectDir();
                    $suites += $config['include'];
                    $this->runIncludedSuites($config['include'], $current_dir);
                }

                if ($this->executed === 0) {
                    throw new \RuntimeException(
                        sprintf("Suite '%s' could not be found", implode(', ', $suites))
                    );
                }
            }

            $this->codecept->printResult();

            if (!$input->getOption('no-exit')) {
                if (!$this->codecept->getResult()->wasSuccessful()) {
                    exit(1);
                }
            }
        }

        private function matchFilteredTestName(&$path)
        {
            if (version_compare(\Codeception\Codecept::VERSION, "2.2.5") >= 0) {
                $test_parts = explode(':', $path, 2);
                if (count($test_parts) > 1) {
                    list($path, $filter) = $test_parts;
                    // use carat to signify start of string like in normal regex
                    // phpunit --filter matches against the fully qualified method name, so tests actually begin with :
                    $carat_pos = strpos($filter, '^');
                    if ($carat_pos !== false) {
                        $filter = substr_replace($filter, ':', $carat_pos, 1);
                    }
                    return $filter;
                }
                return null;
            }
            else {
                $test_parts = explode(':', $path);
                if (count($test_parts) > 1) {
                    list($path, $filter) = $test_parts;
                    return $filter;
                }
                return null;
            }
        }

        private function ensureCurlIsAvailable()
        {
            if (!extension_loaded('curl')) {
                throw new \Exception(
                    "Codeception requires CURL extension installed to make tests run\n"
                    . "If you are not sure, how to install CURL, please refer to StackOverflow\n\n"
                    . "Notice: PHP for Apache/Nginx and CLI can have different php.ini files.\n"
                    . "Please make sure that your PHP you run from console has CURL enabled."
                );
            }
        }
    }

    class PhpStorm_Codeception_Codecept extends \Codeception\Codecept {
        public function __construct($options = [])
        {
            parent::__construct($options);

            $printer = new PhpStorm_Codeception_ReportPrinter();
            $this->runner = new \Codeception\PHPUnit\Runner();
            $this->runner->setPrinter($printer);
        }
    }

    $app->add(new PhpStorm_Codeception_Command_Run('run'));
    $app->run();
}
