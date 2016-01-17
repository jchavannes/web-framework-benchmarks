<?

const NUMBER_OF_CALLS = 100;

echo "Doing " . NUMBER_OF_CALLS . " calls per framework.";

new Benchmark('http://127.0.0.1:8091', 'php-raw');
new Benchmark('http://127.0.0.1:8092', 'php-lumen');
new Benchmark('http://127.0.0.1:8093', 'php-zend');
new Benchmark('http://127.0.0.1:8094', 'nodejs-express');
new Benchmark('http://127.0.0.1:8095', 'nodejs-restify');
new Benchmark('http://127.0.0.1:8096', 'nodejs-raw');

class Benchmark
{
    private $handle;
    private $name;

    private $times = array();

    public function __construct($url, $name)
    {
        $this->name     = $name;
        $this->handle   = curl_init();

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

        for ($i = 0; $i < NUMBER_OF_CALLS; $i++) {
            $result = curl_exec($this->handle);

            if ($result != 'hello world') {
                throw new Exception('Did not receive expected "hello world" response.');
            }

            $this->saveTimes();
        }

        $this->outputTimes();
    }

    private function getChecks()
    {
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

    private function outputTimes()
    {
        echo $this->name . "...\n";

        foreach ($this->getChecks() as $check) {
            echo sprintf('  %-20s', $check . ': ') . array_sum($this->times[$check]) / count($this->times[$check]) * 1000 . "ms\n";
        }
    }
}
