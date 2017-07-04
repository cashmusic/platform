<?php
namespace Kahlan\Reporter;

use Kahlan\Util\Text;

class Bar extends Terminal
{
    /**
     * Color preferences.
     *
     * var array
     */
    protected $_preferences = [];

    /**
     * Format of the progress bar.
     *
     * var string
     */
    protected $_format = '';

    /**
     * Char preferences.
     *
     * var array
     */
    protected $_chars = [];

    /**
     * Progress bar color.
     *
     * var integer
     */
    protected $_color = 37;

    /**
     * Size of the progress bar.
     *
     * var integer
     */
    protected $_size = 0;

    /**
     * Constructor
     *
     * @param array $config The config array.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $defaults = [
            'size' => 50,
            'preferences' => [
                'passed'  => 'green',
                'failed'  => 'red'
            ],
            'chars' => [
                'bar'       => '=',
                'indicator' => '>'
            ],
            'format' => '[{:b}{:i}] {:p}%'
        ];
        $config += $defaults;

        $config['chars'] += $defaults['chars'];
        $config['preferences'] += $defaults['preferences'];

        foreach ($config as $key => $value) {
            $_key = "_{$key}";
            $this->$_key = $value;
        }
        $this->_color = $this->_preferences['passed'];
    }

    /**
     * Callback called before any specs processing.
     *
     * @param array $args The suite arguments.
     */
    public function start($args)
    {
        parent::start($args);
        $this->write("\n");
        $this->_progressBar();
    }

    /**
     * Callback called after a spec execution.
     *
     * @param object $log The log object of the whole spec.
     */
    public function specEnd($log = null)
    {
        $this->_current++;
        switch ($log->type()) {
            case 'failed':
            case 'errored':
                $this->_color = $this->_preferences['failed'];
                break;
        }
        $this->_progressBar();
    }

    /**
     * Ouputs the progress bar to STDOUT.
     */
    protected function _progressBar()
    {
        if ($this->_current > $this->_total) {
            return;
        }

        $percent = $this->_current / $this->_total;
        $nb = $percent * $this->_size;

        $b = str_repeat($this->_chars['bar'], floor($nb));
        $i = '';

        if ($nb < $this->_size) {
            $i = str_pad($this->_chars['indicator'], $this->_size - strlen($b));
        }

        $p = floor($percent * 100);

        $string = Text::insert($this->_format, compact('p', 'b', 'i'));

        $this->write("\r" . $string, $this->_color);
    }

    /**
     * Callback called at the end of specs processing.
     *
     * @param object $summary The execution summary instance.
     */
    public function end($summary)
    {
        $this->write("\n\n");
        foreach ($summary->logs() as $log) {
            if (!$log->passed()) {
                $this->_report($log);
            }
        }
        $this->write("\n\n");
        $this->_reportSummary($summary);
    }
}
