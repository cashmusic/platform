<?php
namespace Kahlan\Reporter\Coverage\Driver;

use Exception;
use RuntimeException;

class Phpdbg
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
        $this->_config = $config;

        if (PHP_SAPI !== 'phpdbg') {
            throw new RuntimeException('PHPDBG SAPI has not been detected.');
        }
    }

    /**
     * Starts code coverage.
     */
    public function start()
    {
        phpdbg_start_oplog();
    }

    /**
     * Stops code coverage.
     *
     * @return array The collected coverage
     */
    public function stop()
    {
        $data = phpdbg_end_oplog();
        $result = [];
        foreach ($data as $file => $coverage) {
            foreach ($coverage as $line => $value) {
                $result[$file][$line - 1] = $value <= 0 ? 0 : 1;
            }
        }
        return $result;
    }
}
