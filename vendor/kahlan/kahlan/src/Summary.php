<?php
namespace Kahlan;

class Summary
{
    /**
     * The log results array.
     *
     * @var array
     */
    protected $_logs = [];

    /**
     * The data results array.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * The memory usage
     *
     * @var integer
     */
    protected $_memoryUsage = 0;

    /**
     * Return the total number of specs.
     *
     * @return integer
     */
    public function total()
    {
        $total = 0;
        foreach ($this->_logs as $key => $value) {
            $total += count($value);
        }
        return $total;
    }

    /**
     * Return the total number of expectations.
     *
     * @return integer
     */
    public function expectation()
    {
        $total = 0;
        foreach ($this->_logs as $key => $value) {
            foreach ($value as $log) {
                $total += count($log->children());
            }
        }
        return $total;
    }

    /**
     * Return the number of executable specs.
     *
     * @return integer
     */
    public function executable()
    {
        return $this->passed() + $this->failed() + $this->errored();
    }

    /**
     * Return the number of specs of a certain type.
     *
     * @return integer
     */
    public function __call($name, $args)
    {
        return isset($this->_logs[$name]) ? count($this->_logs[$name]) : 0;
    }

    /**
     * Add a data to a specific key.
     *
     * @param  string $type  The type of data.
     * @param  mixed  $value The value to add.
     * @return self
     */
    public function add($type, $value)
    {
        if (!isset($this->_data[$type])) {
            $this->_data[$type] = [];
        }
        $this->_data[$type][] = $value;
        return $this;
    }

    /**
     * Get a data of a specific key.
     *
     * @param string $type The type of data.
     * @return array
     */
    public function get($type)
    {
        return isset($this->_data[$type]) ? $this->_data[$type] : [];
    }

    /**
     * Ingest a log.
     *
     * @param  array $log The log report.
     * @return self
     */
    public function log($log)
    {
        $type = $log->type();
        if (!isset($this->_logs[$type])) {
            $this->_logs[$type] = [];
        }
        $this->_logs[$type][] = $log;
        return $this;
    }

    /**
     * Get log report
     *
     * @param  string $type The type of data.
     * @return array
     */
    public function logs($type = null)
    {
        if (func_num_args()) {
            return isset($this->_logs[$type]) ? $this->_logs[$type] : [];
        }
        $logs = [];
        foreach ($this->_logs as $key => $value) {
            $logs = array_merge($logs, $value);
        }
        return $logs;
    }


    /**
     * Return the total number of specs.
     *
     * @return integer
     */
    public function memoryUsage($memoryUsage = null)
    {
        if (!func_get_args()) {
            return $this->_memoryUsage;
        }
        $this->_memoryUsage = $memoryUsage;
        return $this;
    }
}
