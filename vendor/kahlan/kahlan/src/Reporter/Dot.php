<?php
namespace Kahlan\Reporter;

class Dot extends Terminal
{
    /**
     * Store the current number of dots.
     *
     * @var integer
     */
    protected $_counter = 0;

    /**
     * The max number of columns.
     *
     * @var integer
     */
    protected $_columns = 80;

    /**
     * The dot area with.
     *
     * @var integer
     */
    protected $_dotWidth = 0;

    /**
     * Constructor
     *
     * @param array $config The config array. Possible options are:
     *                      -`'columns'`: the max columns width for dot area.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $defaults = ['columns' => 80];
        $config += $defaults;
        $this->_columns = $config['columns'];
    }

    /**
     * Callback called before any specs processing.
     *
     * @param array $args The suite arguments array.
     */
    public function start($args)
    {
        parent::start($args);
        $this->_dotWidth = max($this->_columns - 11 - strlen($this->_total) * 2, 10);
        $this->write("\n");
    }

    /**
     * Callback called after a spec execution.
     *
     * @param object $log The log object of the whole spec.
     */
    public function specEnd($log = null)
    {
        switch ($log->type()) {
            case 'passed':
                $this->_write($this->_symbols['dot']);
                break;
            case 'skipped':
                $this->_write('S', 'light-grey');
                break;
            case 'pending':
                $this->_write('P', 'cyan');
                break;
            case 'excluded':
                $this->_write('X', 'yellow');
                break;
            case 'failed':
                $this->_write('F', 'red');
                break;
            case 'errored':
                $this->_write('E', 'magenta');
                break;
        }
    }

    /**
     * Callback called at the end of specs processing.
     *
     * @param object $summary The execution summary instance.
     */
    public function end($summary)
    {
        do {
            $this->_write(' ');
        } while ($this->_counter % $this->_dotWidth !== 0);

        $this->write("\n");

        foreach ($summary->logs() as $log) {
            if (!$log->passed()) {
                $this->_report($log);
            }
        }

        $this->write("\n\n");
        $this->_reportSummary($summary);
    }

    /**
     * Outputs the string message in the console.
     *
     * @param string       $string  The string message.
     * @param array|string $options The color options.
     */
    protected function _write($string, $options = null)
    {
        $this->write($string, $options);
        $this->_counter++;

        if ($this->_counter % $this->_dotWidth === 0) {
            $counter = min($this->_counter, $this->_total);
            $percent = min(floor(($counter * 100) / $this->_total), 100) . '%';
            $this->write(str_pad($counter, strlen($this->_total) + 1, ' ', STR_PAD_LEFT));
            $this->write(' / ' . $this->_total);
            $this->write(' (' . str_pad($percent, 4, ' ', STR_PAD_LEFT) . ")\n");
        }
    }
}
