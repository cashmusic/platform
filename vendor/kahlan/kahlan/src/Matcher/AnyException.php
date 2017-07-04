<?php
namespace Kahlan\Matcher;

class AnyException extends \Exception
{
    /**
     * The exception message.
     *
     * @var string
     */
    protected $message = null;

    /**
     * The exception message.
     *
     * @param string $message  The exception message.
     * @param string $code     The exception code.
     * @param string $previous The previous exception.
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
