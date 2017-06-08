<?php namespace Pixie\QueryBuilder;

class QueryObject
{

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $bindings = array();

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct($sql, array $bindings, \PDO $pdo)
    {
        $this->sql = (string)$sql;
        $this->bindings = $bindings;
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the raw/bound sql
     *
     * @return string
     */
    public function getRawSql()
    {
        return $this->interpolateQuery($this->sql, $this->bindings);
    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from
     * $params are are in the same order as specified in $query
     *
     * Reference: http://stackoverflow.com/a/1376838/656489
     *
     * @param string $query  The sql query with parameter placeholders
     * @param array  $params The array of substitution parameters
     *
     * @return string The interpolated query
     */
    protected function interpolateQuery($query, $params)
    {
        $keys = array();
        $values = $params;

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_string($value)) {
                $values[$key] = $this->pdo->quote($value);
            }

            if (is_array($value)) {
                $values[$key] = implode(',', $this->pdo->quote($value));
            }

            if (is_null($value)) {
                $values[$key] = 'NULL';
            }
        }

        $query = preg_replace($keys, $values, $query, 1, $count);

        return $query;
    }
}
