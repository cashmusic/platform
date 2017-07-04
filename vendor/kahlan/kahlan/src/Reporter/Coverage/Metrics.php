<?php
namespace Kahlan\Reporter\Coverage;

class Metrics
{
    /**
     * Reference to the parent metrics.
     *
     * @var object
     */
    protected $_parent = null;

    /**
     * The string name reference of the metrics.
     *
     * @var string
     */
    protected $_name = '';

    /**
     * The type of the metrics is about.
     *
     * @var string
     */
    protected $_type = 'namespace';

    /**
     * The metrics data.
     *
     * @var array The metrics:
     *            - `'loc'`      _integer_ : the number of line of code.
     *            - `'lloc'`     _integer_ : the number of logical line of code (i.e code statements or those lines which end in a semicolon)
     *            - `'nlloc'`    _integer_ : the number of non logical line of code (i.e uncoverable).
     *            - `'cloc'`     _integer_ : the number of covered line of code
     *            - `'methods'`  _integer_ : the number of methods.
     *            - `'cmethods'` _integer_ : the number of covered methods.
     *            - `'files'`    _array_   : the file paths.
     */
    protected $_metrics = [
        'loc'      => 0,
        'lloc'     => 0,
        'nlloc'    => 0,
        'cloc'     => 0,
        'coverage' => 0,
        'methods'  => 0,
        'cmethods' => 0,
        'files'    => [],
        'percent'  => 0
    ];

    /**
     * The child metrics of the current metrics.
     *
     * @param array
     */
    protected $_children = [];

    /**
     * Constructor
     *
      * @param array $options Possible options values are:
     *                        - `'name'`   _string_  : the string name reference of the metrics.
     *                        - `'type'`   _string_  : the type of the metrics is about.
     *                        - `'parent'` _instance_: reference to the parent metrics.
     */
    public function __construct($options = [])
    {
        $defaults = ['name' => '', 'type' => 'namespace', 'parent' => null];
        $options += $defaults;

        $this->_parent = $options['parent'];
        $this->_type = $options['type'];

        if (!$this->_parent) {
            $this->_name = $options['name'];
            return;
        }

        $pname =  $this->_parent->name();
        switch ($this->_type) {
            case 'namespace':
            case 'function':
            case 'trait':
            case 'class':
                $this->_name = $pname ? $pname . $options['name'] : $options['name'];
                break;
            case 'method':
                $this->_name = $pname ? $pname . '::' . $options['name'] : $options['name'];
                break;
        }
    }

    /**
     * Gets the parent instance.
     *
     * @return object The parent instance
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * Gets the name of the metrics.
     *
     * @return string The name of the metrics.
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * Gets the type of the metrics.
     *
     * @return string The type of the metrics.
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * Gets/Sets the metrics stats.
     *
     * @param  array $metrics The metrics data to set if defined.
     * @return array          The metrics data.
     */
    public function data($metrics = [])
    {
        if (func_num_args() === 0) {
            return $this->_metrics;
        }

        $this->_metrics = $metrics + $this->_metrics;

        if ($this->_metrics['lloc']) {
            $this->_metrics['percent'] = ($this->_metrics['cloc'] * 100) / $this->_metrics['lloc'];
        } else {
            $this->_metrics['percent'] = 100;
        }
    }

    /**
     * Adds some metrics to the current metrics.
     *
     * @param string $name The name reference of the metrics.
     *                     Possible values are: `'namespace'`, `'class' or 'function'.
     * @param array        The metrics array to add.
     */
    public function add($name, $metrics)
    {
        $parts = $this->_parseName($name);
        $this->_merge($metrics);

        $current = $this;
        $length = count($parts);
        foreach ($parts as $index => $part) {
            list($name, $type) = $part;
            if (!isset($current->_children[$name])) {
                $current->_children[$name] = new static([
                    'name'   => $name,
                    'parent' => $current,
                    'type'   => $type
                ]);
            }
            uksort($current->_children, function ($a, $b) {
                $isFunction1 = substr($a, -2) === '()';
                $isFunction2 = substr($b, -2) === '()';
                if ($isFunction1 === $isFunction2) {
                    return strcmp($a, $b);
                }
                return $isFunction1 ? -1 : 1;
            });

            $current = $current->_children[$name];
            $current->_merge($metrics, $index === $length - 1);
        }
    }

    /**
     * Gets the metrics from a name.
     *
     * @param  string $name The name reference of the metrics.
     * @return object       The metrics instance.
     */
    public function get($name = null)
    {
        $parts = $this->_parseName($name);

        $child = $this;
        foreach ($parts as $part) {
            list($name, $type) = $part;
            if (!isset($child->_children[$name])) {
                return;
            }
            $child = $child->_children[$name];
        }
        return $child;
    }

    /**
     * Gets the children of the current metrics.
     *
     * @param  string $name The name reference of the metrics.
     * @return array        The metrics children.
     */
    public function children($name = null)
    {
        $child = $this->get($name);
        if (!$child) {
            return [];
        }
        return $child->_children;
    }

    /**
     * Gets meta info of a metrics from a name reference..
     *
     * @param  string $name The name reference of the metrics.
     * @param  string $type The type to use by default if not auto detected.
     * @return array        The parsed name.
     */
    protected function _parseName($name)
    {
        $result = [];
        $parts = preg_split('~([^\\\]*\\\?)~', $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $last = array_pop($parts);

        if (!$last) {
            return [];
        }

        foreach ($parts as $name) {
            $result[] = [$name, 'namespace'];
        }

        if (strpos($last, '::') !== false) {
            list($name, $subname) = explode('::', $last, 2);
            $result[] = [$name, 'class'];
            $result[] = [$subname, 'method'];
        } elseif (preg_match('~\(\)$~', $last)) {
            $result[] = [$last, 'function'];
        } else {
            $result[] = [$last, substr($last, -1) === '\\' ? 'namespace' : 'class'];
        }
        return $result;
    }

    /**
     * Merges some given metrics to the existing metrics .
     *
     * @param array   $metrics Metrics data to merge.
     * @param boolean $line    Set to `true` for function only
     */
    protected function _merge($metrics = [], $line = false)
    {
        $defaults = [
            'loc'      => [],
            'nlloc'    => [],
            'lloc'     => [],
            'cloc'     => [],
            'files'    => [],
            'methods'  => 0,
            'cmethods' => 0
        ];
        $metrics += $defaults;

        foreach (['loc', 'nlloc', 'lloc', 'cloc', 'coverage', 'files', 'methods', 'cmethods'] as $name) {
            $metrics[$name] += $this->_metrics[$name];
        }
        if (!$line) {
            unset($metrics['line']);
        }
        $this->data($metrics);
    }
}
