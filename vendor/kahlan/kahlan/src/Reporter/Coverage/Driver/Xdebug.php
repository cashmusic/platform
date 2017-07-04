<?php
namespace Kahlan\Reporter\Coverage\Driver;

use Exception;
use RuntimeException;

class Xdebug
{
    /**
     * Config array
     *
     * @var array
     */
    protected $_config = [];

    /**
     * The Constructor.
     *
     * @param array $config The options array, possible options are:
     *                      - `'cleanup'`  _boolean_: indicated if the coverage should be flushed on stop.
     *                      - `'coverage'` _integer_: the code coverage mask.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'coverage' => 0,
            'cleanup' => true
        ];
        $this->_config = $config + $defaults;

        if (!extension_loaded('xdebug')) {
            throw new RuntimeException('Xdebug is not loaded.');
        }

        if (!defined('HHVM_VERSION') && !ini_get('xdebug.coverage_enable')) {
            throw new RuntimeException('You need to set `xdebug.coverage_enable = On` in your php.ini.');
        }
    }

    /**
     * Starts code coverage.
     */
    public function start()
    {
        //@see bug https://github.com/facebook/hhvm/issues/4752
        try {
            xdebug_start_code_coverage($this->_config['coverage']);
        } catch (Exception $e) {
        }
    }

    /**
     * Stops code coverage.
     *
     * @return array The collected coverage
     */
    public function stop()
    {
        $data = xdebug_get_code_coverage();
        xdebug_stop_code_coverage($this->_config['cleanup']);

        $result = [];
        foreach ($data as $file => $coverage) {
            foreach ($coverage as $line => $value) {
                if ($line && $value !== -2) {
                    $result[$file][$line - 1] = $value === -1 ? 0 : $value;
                }
            }
        }
        return $result;
    }
}
