<?php
namespace Kahlan\Reporter;

use Kahlan\Util\Text;

class Json extends Terminal
{
    /**
     * Store the current number of dots.
     *
     * @var integer
     */
    protected $_counter = 0;

    /**
     * Store schema for JSON output
     *
     * @var array
     */
    protected $_json = [
        'errors'  => []
    ];

    /**
     * Callback called before any specs processing.
     *
     * @param array $args The suite arguments.
     */
    public function start($args)
    {
        $this->_header = false;
        parent::start($args);
    }

    /**
     * Callback called at the end of specs processing.
     *
     * @param object $summary The execution summary instance.
     */
    public function end($summary)
    {
        $toString = function ($instance) {
            return 'an instance of `' . get_class($instance) . '`';
        };

        foreach ($summary->logs() as $log) {
            if ($log->passed()) {
                continue;
            }
            switch ($log->type()) {
                case 'failed':
                    foreach ($log->children() as $log) {
                        if ($log->passed()) {
                            continue;
                        }
                        $data = [];
                        foreach ($log->data() as $key => $value) {
                            $data[$key] = Text::toString($value, ['object' => ['method' => $toString]]);
                        }

                        $this->_json['errors'][] = [
                            'spec'  => trim(implode(' ', $log->messages())),
                            'suite' => $log->file(),
                            'data'  => $data
                        ];
                    }
                    break;
                case 'errored':
                    $exception = $log->exception();

                    $this->_json['errors'][] = [
                        'spec' => trim(implode(' ', $log->messages())),
                        'suite' => $log->file(),
                        'exception' => '`' . get_class($exception) .'` Code(' . $exception->getCode() . ')',
                        'trace' => $exception->getMessage()
                    ];
                    break;
            }
        }
        $this->_json['summary'] = [
            'total'    => $summary->total(),
            'passed'   => $summary->passed(),
            'pending'  => $summary->pending(),
            'skipped'  => $summary->skipped(),
            'excluded' => $summary->excluded(),
            'failed'   => $summary->failed(),
            'errored'  => $summary->errored(),
        ];

        $this->write(json_encode($this->_json));
    }
}
