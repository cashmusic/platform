<?php
namespace Kahlan\Reporter;

use Kahlan\Reporter\Coverage\Collector;

class Coverage extends Terminal
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected static $_classes = [
        'interceptor' => 'Kahlan\Jit\Interceptor'
    ];

    /**
     * Collect time.
     *
     * @var float
     */
    protected $_time = 0;

    /**
     * The coverage verbosity.
     *
     * @param integer
     */
    protected $_verbosity = 0;

    /**
     * Reference to the coverage collector driver.
     *
     * @param object
     */
    protected $_collector = '';

    /**
     * Status of the reporter.
     *
     * @var array
     */
    protected $_enabled = true;

    /**
     * Store prefix by level for tree rendering.
     *
     * @var array
     */
    protected $_prefixes = [];

    /**
     * The Constructor.
     *
     * @param array $config The config for the reporter, the options are:
     *                      - `'verbosity`' _integer|string_: The verbosity level:
     *                        - 1      : overall coverage value for the whole code.
     *                        - 2      : overall coverage by namespaces.
     *                        - 3      : overall coverage by classes.
     *                        - 4      : overall coverage by methods and functions.
     *                        - string : coverage for a fully namespaced (class/method/namespace) string.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $defaults = ['verbosity' => 1];
        $config += $defaults;

        $verbosity = $config['verbosity'];
        $this->_verbosity  = is_numeric($verbosity) ? (integer) $verbosity : (string) $verbosity;

        if (is_string($this->_verbosity)) {
            $class = preg_replace('/(::)?\w+\(\)$/', '', $this->_verbosity);
            $interceptor = static::$_classes['interceptor'];
            $loader = $interceptor::instance();

            if ($loader && $path = $loader->findPath($class)) {
                $config['path'] = $path;
            }
        }

        $this->_collector = new Collector($config);
    }

    /**
     * Callback called before any specs processing.
     *
     * @param array $args The suite arguments.
     */
    public function start($args)
    {
    }

    /**
     * Callback called on a spec start.
     *
     * @param object $spec The spec object of the whole spec.
     */
    public function specStart($spec = null)
    {
        parent::specStart($spec);
        if (!$this->enabled()) {
            return;
        }
        $this->_collector->start();
    }

    /**
     * Callback called after a spec execution.
     *
     * @param object $log The log object of the whole spec.
     */
    public function specEnd($log = null)
    {
        parent::specEnd($log);
        if (!$this->enabled()) {
            return;
        }
        $this->_collector->stop();
    }

    /**
     * Gets the collector.
     *
     * @return object
     */
    public function collector()
    {
        return $this->_collector;
    }

    /**
     * Delegates the call to the collector instance.
     *
     * @param  string  $name The function name.
     * @param  array   $args The arguments to pass to the function.
     * @return mixed
     */
    public function __call($name, $args)
    {
        return call_user_func_array([$this->collector(), $name], $args);
    }

    /**
     * Gets the metrics about the coverage result.
     */
    public function metrics()
    {
        $this->_start = microtime(true);
        $result = $this->_collector->metrics();
        $this->_time  = microtime(true) - $this->_start;
        return $result;
    }

    /**
     * Outputs some metrics info where the metric is not the total coverage.
     *
     * @param Metrics $metrics A metrics instance.
     * @param array   $options The options for the reporter, the options are:
     *                         - `'verbosity`' _integer|string_: The verbosity level:
     *                           - 1      : overall coverage value for the whole code.
     *                           - 2      : overall coverage by namespaces.
     *                           - 3      : overall coverage by classes.
     *                           - 4      : overall coverage by methods and functions.
     *                           - string : coverage for a fully namespaced (class/method/namespace) string.
     */
    protected function _renderMetrics($metrics, $verbosity)
    {
        $maxLabelWidth = null;
        if ($verbosity === 1) {
            return;
        }
        $metricsReport = $this->_getMetricsReport($metrics->children(), $verbosity, 0, 3, $maxLabelWidth);
        $name = $metrics->name() ?: '\\';
        $maxLabelWidth = max(strlen($name) + 1, $maxLabelWidth);
        $maxLabelWidth += 4;
        $stats = $metrics->data();
        $percent = number_format($stats['percent'], 2);
        $style = $this->_style($percent);
        $maxLineWidth = strlen($stats['lloc']);

        $this->write(str_repeat(' ', $maxLabelWidth));
        $this->write('  ');
        $this->write(str_pad('Lines', $maxLineWidth * 2 + 3, ' ', STR_PAD_BOTH));
        $this->write(str_pad('%', 12, ' ', STR_PAD_LEFT));
        $this->write("\n\n");
        $this->write(str_pad(' ' . $name, $maxLabelWidth));
        $this->write('  ');
        $this->write(str_pad("{$stats['cloc']}", $maxLineWidth, ' ', STR_PAD_LEFT));
        $this->write(' / ');
        $this->write(str_pad("{$stats['lloc']}", $maxLineWidth, ' ', STR_PAD_LEFT));
        $this->write('     ');
        $this->write(str_pad("{$percent}%", 7, ' ', STR_PAD_LEFT), $style);
        $this->write("\n");
        $this->_renderMetricsReport($metricsReport, $maxLabelWidth, $maxLineWidth, 0);
    }

    /**
     * Outputs some metrics reports built using `::_getMetricsReport()`.
     *
     * @param array $metricsReport An array of nested metrics reports extracted according some verbosity.
     * @param array $labelWidth    The width column of the label column used for padding.
     * @param array $lineWidth     The width column of the covered lines data used for padding.
     * @param array $depth         The actual depth in the reporting to build tree prefix.
     */
    protected function _renderMetricsReport($metricsReport, $labelWidth, $lineWidth, $depth)
    {
        $nbChilden = count($metricsReport);
        $index = 0;
        foreach ($metricsReport as $name => $data) {
            $isLast = $index === $nbChilden - 1;
            if ($isLast) {
                $this->_prefixes[$depth] = '└──';
            } else {
                $this->_prefixes[$depth] = '├──';
            }

            $metrics = $data['metrics'];
            $stats = $metrics->data();
            $percent = number_format($stats['percent'], 2);
            $style = $this->_style($percent);

            $prefix = join('', $this->_prefixes) . ' ';
            $diff = strlen($prefix) - strlen(utf8_decode($prefix));

            $type = $metrics->type();
            $color = $type === 'function' || $type === 'method' ? 'd' : '';
            $this->write($prefix);
            $this->write(str_pad($name, $labelWidth + $diff - strlen($prefix)), $color);
            $this->write('  ');
            $this->write(str_pad("{$stats['cloc']}", $lineWidth, ' ', STR_PAD_LEFT));
            $this->write(' / ');
            $this->write(str_pad("{$stats['lloc']}", $lineWidth, ' ', STR_PAD_LEFT));
            $this->write('     ');
            $this->write(str_pad("{$percent}%", 7, ' ', STR_PAD_LEFT), $style);
            $this->write("\n");

            if ($isLast) {
                $this->_prefixes[$depth] = '   ';
            } else {
                $this->_prefixes[$depth] = '│  ';
            }
            $this->_renderMetricsReport($data['children'], $labelWidth, $lineWidth, $depth + 1);
            $index++;
        }
        $this->_prefixes[$depth] = '';
    }

    /**
     * Extract some metrics reports to display according to a verbosity parameter.
     *
     * @param Metrics[] $children A array of metrics.
     * @param array     $options  The options for the reporter, the options are:
     *                            - `'verbosity`' _integer|string_: The verbosity level:
     *                              - 1      : overall coverage value for the whole code.
     *                              - 2      : overall coverage by namespaces.
     *                              - 3      : overall coverage by classes.
     *                              - 4      : overall coverage by methods and functions.
     *                              - string : coverage for a fully namespaced (class/method/namespace) string.
     * @param array     $depth     The actual depth in the reporting.
     * @param array     $tab       The size of the tab used for lablels.
     * @param array     $maxWidth  Will contain the maximum width obtained for labels.
     */
    protected function _getMetricsReport($children, $verbosity, $depth = 0, $tab = 3, &$maxWidth = null)
    {
        $list = [];
        foreach ($children as $child) {
            $type = $child->type();

            if ($verbosity === 2 && $type !== 'namespace') {
                continue;
            }
            if ($verbosity === 3 && ($type === 'function' || $type === 'method')) {
                continue;
            }

            $name = $child->name();

            if ($name !== '\\') {
                $pos = strrpos($name, '\\', $type === 'namespace' ? - 2 : 0);
                $basename = substr($name, $pos !== false ? $pos + 1 : 0);
            } else {
                $basename = '\\';
            }

            $len = strlen($basename) + ($depth + 1) * $tab;
            if ($len > $maxWidth) {
                $maxWidth = $len;
            }
            $list[$basename] = [
                'metrics'  => $child,
                'children' => $this->_getMetricsReport($child->children(), $verbosity, $depth + 1, $tab, $maxWidth)
            ];
        }
        return $list;
    }

    /**
     * Outputs the coverage report of a metrics instance.
     *
     * @param Metrics $metrics A metrics instance.
     */
    protected function _renderCoverage($metrics)
    {
        $stats = $metrics->data();
        foreach ($stats['files'] as $file) {
            $this->write("File: {$file}" . "\n\n");

            $lines = file($file);

            $coverage = $this->_collector->export($file);

            if (isset($stats['line'])) {
                $start = $stats['line']['start'];
                $stop = $stats['line']['stop'];
            } else {
                $start = 0;
                $stop = count($lines) - 1;
            }

            for ($i = $start; $i <= $stop; $i++) {
                $value = isset($coverage[$i]) ? $coverage[$i] : null;
                $line = str_pad($i + 1, 6, ' ', STR_PAD_LEFT);
                $line .= ':' . str_pad($value, 6, ' ');
                $line .= $lines[$i];
                if ($value) {
                    $this->write($line, 'n;green');
                } elseif ($value === 0) {
                    $this->write($line, 'n;red');
                } else {
                    $this->write($line);
                }
            }
            $this->write("\n\n");
        }
    }

    /**
     * Helper determinig a color from a coverage rate.
     *
     * @param integer $percent The coverage rate in percent.
     */
    protected function _style($percent)
    {
        switch (true) {
            case $percent >= 80:
                return 'n;green';
            break;
            case $percent >= 60:
                return 'n;default';
            break;
            case $percent >= 40:
                return 'n;yellow';
            break;
        }
        return 'n;red';
    }

    /**
     * Callback called at the end of the process.
     *
     * @param object $summary The execution summary instance.
     */
    public function stop($summary)
    {
        $this->write("Coverage Summary\n----------------\n");

        $verbosity = $this->_verbosity;
        $metrics = is_numeric($this->_verbosity) ? $this->metrics() : $this->metrics()->get($verbosity);

        if (!$metrics) {
            $this->write("\nUnexisting namespace/reference: `{$this->_verbosity}`, coverage can't be generated.\n\n", "n;yellow");
            return;
        }

        $this->_renderMetrics($metrics, $verbosity);
        $this->write("\n");

        if (is_string($verbosity)) {
            $this->_renderCoverage($metrics);
            $this->write("\n");
        }

        // Output the original stored metrics object (the total coverage)
        $name = $metrics->name();
        $stats = $metrics->data();
        $percent = number_format($stats['percent'], 2);
        $this->write(str_repeat('  ', substr_count($name, '\\')));

        $pos = strrpos($name, '\\');
        $basename = substr($name, $pos !== false ? $pos + 1 : 0);
        $this->write('Total: ');
        $this->write("{$percent}% ", $this->_style($percent));
        $this->write("({$stats['cloc']}/{$stats['lloc']})");
        $this->write("\n");

        // Output the time to collect coverage
        $time = number_format($this->_time, 3);
        $memory = $this->readableSize(memory_get_peak_usage() - $summary->memoryUsage());
        $this->write("\nCoverage collected in {$time} seconds (using an additionnal {$memory}o)\n\n\n");
    }

    /**
     * Gets the status of the reporter.
     *
     * @return boolean $active
     */
    public function enabled()
    {
        return $this->_enabled;
    }

    /**
     * Gets this reporter.
     */
    public function enable()
    {
        $this->_enabled = true;
        $this->_collector->start();
    }

    /**
     * Disables this reporter.
     */
    public function disable()
    {
        $this->_enabled = false;
        $this->_collector->stop();
    }
}
