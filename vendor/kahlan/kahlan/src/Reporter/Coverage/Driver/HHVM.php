<?php
namespace Kahlan\Reporter\Coverage\Driver;

use RuntimeException;

class HHVM
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
     *                      - `'cleanup'` _boolean_: indicated if the coverage should be flushed on stop.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'cleanup' => true
        ];
        $this->_config = $config;

        if (!defined('HHVM_VERSION')) {
            throw new RuntimeException('HHVM is not loaded.');
        }
    }

    /**
     * Starts code coverage.
     */
    public function start()
    {
        //@see bug https://github.com/facebook/hhvm/issues/4752
        try {
            fb_enable_code_coverage();
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
        $data = fb_get_code_coverage($this->_config['cleanup']);
        fb_disable_code_coverage();

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
