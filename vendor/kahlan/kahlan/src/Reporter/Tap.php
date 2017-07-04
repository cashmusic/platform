<?php
namespace Kahlan\Reporter;

use Kahlan\Util\Text;

class Tap extends Terminal
{
    /**
     * Store the current spec number.
     *
     * @var integer
     */
    protected $_counter = 0;

    /**
     * Callback called before any specs processing.
     *
     * @param array $args The suite arguments.
     */
    public function start($args)
    {
        $this->_header = false;
        parent::start($args);
        $this->write("\n1..{$args['total']}\n");
    }

    /**
     * Callback called after a spec execution.
     *
     * @param object $log The log object of the whole spec.
     */
    public function specEnd($log = null)
    {
        $isOk = $log->passed() ? "ok" : "not ok";

        switch ($log->type()) {
            case 'skipped':
            case 'pending':
            case 'excluded':
                $prefix = "# {$log->type()} ";
                break;
            default:
                $prefix = '- ';
                break;
        }
        $message = $prefix . trim(implode(" ", $log->messages()));
        $this->_counter++;

        $this->write("{$isOk} {$this->_counter} {$message}\n");

        if ($exception = $log->exception()) {
            $this->write('# Exception: `' . get_class($exception) .'` Code(' . $exception->getCode() . '):' . "\n");
            $this->write('# Message: ' . $exception->getMessage() . "\n");
        } else {
            foreach ($log->children() as $log) {
                if ($log->passed()) {
                    continue;
                }
                $toString = function ($instance) {
                    return 'an instance of `' . get_class($instance) . '`';
                };
                foreach ($log->data() as $key => $value) {
                    $key = ucfirst($key);
                    $value = Text::toString($value, ['object' => ['method' => $toString]]);
                    $this->write("# {$key}: {$value}\n");
                }
            }
        }
    }

    /**
     * Callback called at the end of specs processing.
     *
     * @param object $summary The execution summary instance.
     */
    public function end($summary)
    {
        $this->write("# total {$summary->total()}\n");
        $this->write("# passed {$summary->passed()}\n");
        $this->write("# pending {$summary->pending()}\n");
        $this->write("# skipped {$summary->skipped()}\n");
        $this->write("# excluded {$summary->excluded()}\n");
        $this->write("# failed {$summary->failed()}\n");
        $this->write("# errored {$summary->errored()}\n");
    }
}
