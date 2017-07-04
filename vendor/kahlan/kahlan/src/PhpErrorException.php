<?php
namespace Kahlan;

class PhpErrorException extends \Exception
{
    /**
     * The exception message.
     *
     * @var string
     */
    protected $message;

    /**
     * The exception code.
     *
     * @var string
     */
    protected $code;

    /**
     * The exception file.
     *
     * @var string
     */
    protected $file;

    /**
     * The exception line.
     *
     * @var string
     */
    protected $line;

    /**
     * The exception trace.
     *
     * @var string
     */
    protected $trace;

    /**
     * The exception message.
     *
     * @param string $message  The exception message.
     * @param string $code     The exception code.
     * @param string $previous The previous exception.
     */
    public function __construct($config = [], $code = 0)
    {
        if (is_string($config)) {
            $this->message = $config;
            $this->code    = $code;
        }
        if (!is_array($config)) {
            return;
        }
        $defaults = [
            'message' => '',
            'code'    => 0,
            'file'    => '',
            'line'    => 0,
            'trace'   => []
        ];
        $config += $defaults;

        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
