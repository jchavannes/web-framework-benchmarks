#!/usr/bin/php
<?

$argParser  = new ArgParser();
$command    = $argParser->getCommand();

if ($command == ArgParser::CMD_START) {
    Container::checkDocker();
    foreach (Framework::getFrameworks() as $framework) {
        $container = new Container($framework);
        $container->run();
    }
}
else if ($command == ArgParser::CMD_STOP) {
    Container::checkDocker();
    foreach (Framework::getFrameworks() as $framework) {
        $container = new Container($framework);
        $container->stop();
    }
}
else if ($command == ArgParser::CMD_BENCHMARK) {
    out("Doing " . Benchmark::NUMBER_OF_CALLS . " calls per framework. Displaying averages.");
    foreach (Framework::getFrameworks() as $framework) {
        $benchmark = new Benchmark($framework);
        $benchmark->run()->outputTimes();
    }
}
else {
    out("== Available commands: ==");
    out(ArgParser::CMD_BENCHMARK);
    out(ArgParser::CMD_START);
    out(ArgParser::CMD_STOP);
}


class Container
{
    /** @var Framework */
    private $framework;
    /** @var string */
    private $directory;

    /**
     * @param Framework $framework
     */
    public function __construct($framework)
    {
        $this->framework = $framework;
        $this->directory = realpath(__DIR__) . '/' . $this->getImageName();
    }

    /**
     * @return string
     */
    private function getImageName()
    {
        return $this->framework->getName();
    }

    /**
     * @return int
     */
    private function getPort()
    {
        return $this->framework->getPort();
    }

    public function run()
    {
        $this->stop();
        $this->build();
        $this->start();
    }

    private function build()
    {
        verbose('Building ' . $this->getImageName() . "...");
        $command = new Command('sudo docker build -t ' . $this->getImageName() . ' ' . $this->directory . '/');
        verbose($command->getOutputString());
        if ($command->exitCode !== 0) {
            throw new Exception('Error building image');
        }
        verbose('Image successfully built: ' . $this->getImageName());
    }

    private function start()
    {
        verbose('Running ' . $this->getImageName() . '...');
        $cmd = 'sudo docker run -v ' . $this->directory . '/www:/var/www -p ' . $this->getPort() . ':80';
        if (ArgParser::isInteractiveMode()) {
            $cmd .= ' -i -t ' . $this->getImageName() . ' /bin/bash';
            new Command($cmd, true);
        }
        else {
            $cmd .= ' -d ' . $this->getImageName();
            $command = new Command($cmd);
            if ($command->exitCode !== 0) {
                throw new Exception('Error running container');
            }
            out($this->getImageName() . ' running successfully at: http://localhost:' . $this->getPort());
        }
    }

    public function stop()
    {
        $command = new Command('sudo docker ps | grep " ' . $this->getImageName() . ':" | awk \'{print $1}\'');
        $currentContainerHash = $command->getOutputString();

        if (!empty($currentContainerHash)) {
            out('Stopping ' . $this->getImageName() . ' (' . $currentContainerHash . ")...");
            new Command('sudo docker stop -t=2 ' . $currentContainerHash);
        }
    }

    static public function checkDocker()
    {
        $command = new Command('type docker');
        if ($command->exitCode !== 0) {
            throw new Exception("Docker must be installed.");
        }
    }
}

class Command
{
    /** @var array */
    public $output;
    /** @var int */
    public $exitCode;

    /**
     * @param string $command
     * @param bool $passThrough
     */
    public function __construct($command, $passThrough = false)
    {
        if ($passThrough) {
            passthru($command, $this->exitCode);
            $this->output = array();
        }
        else {
            exec($command, $this->output, $this->exitCode);
        }
    }

    public function getOutputString()
    {
        return implode("\n", $this->output);
    }
}

class Framework
{
    /** @var string */
    private $name;
    /** @var int */
    private $port;

    /**
     * @param string $name
     * @param int $port
     */
    public function __construct($name, $port)
    {
        $this->name = $name;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return 'http://127.0.0.1:' . $this->port;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Framework[]
     * @throws Exception
     */
    public static function getFrameworks()
    {
        $frameworks = array(
            'php5-raw'          => new Framework('php5-raw',       8091),
            'php5-lumen'        => new Framework('php5-lumen',     8092),
            'php5-zend'         => new Framework('php5-zend',      8093),
            'nodejs-express'    => new Framework('nodejs-express', 8094),
            'nodejs-restify'    => new Framework('nodejs-restify', 8095),
            'nodejs-raw'        => new Framework('nodejs-raw',     8096),
        );
        $framework = ArgParser::getFramework();
        if (isset($frameworks[$framework])) {
            return array(
                $frameworks[$framework],
            );
        }
        else if (!empty($framework)) {
            throw new Exception('Unable to find framework: ' . $framework);
        }
        else {
            return array_values($frameworks);
        }
    }
}

class Benchmark
{
    const NUMBER_OF_CALLS = 100;

    /** @var resource */
    private $handle;
    /** @var string */
    private $name;
    /** @var bool */
    private $displayAllTimes = false;
    /** @var string[int] */
    private $times = array();

    /**
     * @param Framework $framework
     */
    public function __construct($framework)
    {
        $this->name     = $framework->getName();
        $this->handle   = curl_init();

        curl_setopt($this->handle, CURLOPT_URL, $framework->getUrl());
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * @throws Exception
     * @return Benchmark $this
     */
    public function run()
    {
        for ($i = 0; $i < self::NUMBER_OF_CALLS; $i++) {
            $result = curl_exec($this->handle);

            if ($result != 'hello world') {
                throw new Exception('Did not receive expected "hello world" response.');
            }

            $this->saveTimes();
        }
        return $this;
    }

    /**
     * @param $displayAllTimes
     * @return Benchmark $this
     */
    public function setDisplayAllTimes($displayAllTimes)
    {
        $this->displayAllTimes = $displayAllTimes;
        return $this;
    }

    /**
     * @return string[]
     */
    private function getChecks()
    {
        if ($this->displayAllTimes) {
            return array(
                'total_time',
            );
        }

        return array(
            'namelookup_time',
            'connect_time',
            'pretransfer_time',
            'starttransfer_time',
            'total_time',
        );
    }

    private function saveTimes()
    {
        $info = curl_getinfo($this->handle);
        foreach ($this->getChecks() as $check) {
            if (!isset($this->times[$check])) {
                $this->times[$check] = array();
            }
            $this->times[$check][] = $info[$check];
        }
    }

    public function outputTimes()
    {
        if (empty($this->times)) {
            out('Run benchmark first.');
            return;
        }

        if (!$this->displayAllTimes) {
            $check = 'total_time';
            out($this->getCheckString($this->name, $this->getAverageTime($check)));
            return;
        }

        out($this->name . "...");

        foreach ($this->getChecks() as $check) {
            out($this->getCheckString("  " . $check, $this->getAverageTime($check)));
        }
    }

    /**
     * @param string $check
     * @return float
     */
    private function getAverageTime($check)
    {
        return array_sum($this->times[$check]) / count($this->times[$check]);
    }

    /**
     * @param string $check
     * @param double $value
     * @return string
     */
    private function getCheckString($check, $value)
    {
        return sprintf('%-20s', $check . ': ') . $value * 1000 . "ms";
    }
}

class ArgParser
{
    const CMD_BENCHMARK = 'benchmark';
    const CMD_START     = 'start';
    const CMD_STOP      = 'stop';

    /** @var string[] */
    private $argv;
    /** @var bool */
    public $interactiveMode    = false;
    /** @var bool */
    public $verboseMode        = false;
    /** @var bool|string */
    public $framework          = false;

    /** @var ArgParser */
    static private $argParser;

    public function __construct()
    {
        global $argv;
        $this->argv = $argv;

        $this->interactiveMode  = in_array('-i', $argv);
        $this->verboseMode      = in_array('-v', $argv);
        $frameworkIndex = array_search('-f', $argv);
        if (!empty($frameworkIndex) && isset($argv[$frameworkIndex + 1])) {
            $this->framework = $argv[$frameworkIndex + 1];
        }
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return isset($this->argv[1]) ? $this->argv[1] : '';
    }

    /**
     * @return ArgParser
     */
    static private function getArgParser()
    {
        if (empty(self::$argParser)) {
            self::$argParser = new self();
        }
        return self::$argParser;
    }

    /**
     * @return bool
     */
    static public function isInteractiveMode()
    {
        return self::getArgParser()->interactiveMode;
    }

    /**
     * @return bool
     */
    static public function isVerboseMode()
    {
        return self::getArgParser()->verboseMode;
    }

    /**
     * @return bool|string
     */
    static public function getFramework()
    {
        return self::getArgParser()->framework;
    }
}

function out($message)
{
    if (!empty($message)) {
        echo $message . "\n";
    }
}

function verbose($message)
{
    if (ArgParser::isVerboseMode()) {
        out($message);
    }
}
