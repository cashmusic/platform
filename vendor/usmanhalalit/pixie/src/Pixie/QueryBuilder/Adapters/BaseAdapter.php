<?php namespace Pixie\QueryBuilder\Adapters;

use Pixie\Connection;
use Pixie\Exception;
use Pixie\QueryBuilder\Raw;

abstract class BaseAdapter
{
    /**
     * @var \Pixie\Connection
     */
    protected $connection;

    /**
     * @var \Viocon\Container
     */
    protected $container;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->container = $this->connection->getContainer();
    }

    /**
     * Build select query string and bindings
     *
     * @param $statements
     *
     * @throws Exception
     * @return array
     */
    public function select($statements)
    {
        if (!array_key_exists('tables', $statements)) {
            throw new Exception('No table specified.', 3);
        } elseif (!array_key_exists('selects', $statements)) {
            $statements['selects'][] = '*';
        }

        // From
        $tables = $this->arrayStr($statements['tables'], ', ');
        // Select
        $selects = $this->arrayStr($statements['selects'], ', ');


        // Wheres
        list($whereCriteria, $whereBindings) = $this->buildCriteriaWithType($statements, 'wheres', 'WHERE');
        // Group bys
        $groupBys = '';
        if (isset($statements['groupBys']) && $groupBys = $this->arrayStr($statements['groupBys'], ', ')) {
            $groupBys = 'GROUP BY ' . $groupBys;
        }

        // Order bys
        $orderBys = '';
        if (isset($statements['orderBys']) && is_array($statements['orderBys'])) {
            foreach ($statements['orderBys'] as $orderBy) {
                $orderBys .= $this->wrapSanitizer($orderBy['field']) . ' ' . $orderBy['type'] . ', ';
            }

            if ($orderBys = trim($orderBys, ', ')) {
                $orderBys = 'ORDER BY ' . $orderBys;
            }
        }

        // Limit and offset
        $limit = isset($statements['limit']) ? 'LIMIT ' . $statements['limit'] : '';
        $offset = isset($statements['offset']) ? 'OFFSET ' . $statements['offset'] : '';

        // Having
        list($havingCriteria, $havingBindings) = $this->buildCriteriaWithType($statements, 'havings', 'HAVING');

        // Joins
        $joinString = $this->buildJoin($statements);

        $sqlArray = array(
            'SELECT' . (isset($statements['distinct']) ? ' DISTINCT' : ''),
            $selects,
            'FROM',
            $tables,
            $joinString,
            $whereCriteria,
            $groupBys,
            $havingCriteria,
            $orderBys,
            $limit,
            $offset
        );

        $sql = $this->concatenateQuery($sqlArray);

        $bindings = array_merge(
            $whereBindings,
            $havingBindings
        );

        return compact('sql', 'bindings');
    }

    /**
     * Build just criteria part of the query
     *
     * @param      $statements
     * @param bool $bindValues
     *
     * @return array
     */
    public function criteriaOnly($statements, $bindValues = true)
    {
        $sql = $bindings = array();
        if (!isset($statements['criteria'])) {
            return compact('sql', 'bindings');
        }

        list($sql, $bindings) = $this->buildCriteria($statements['criteria'], $bindValues);

        return compact('sql', 'bindings');
    }

    /**
     * Build a generic insert/ignore/replace query
     *
     * @param       $statements
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    private function doInsert($statements, array $data, $type)
    {
        if (!isset($statements['tables'])) {
            throw new Exception('No table specified', 3);
        }

        $table = end($statements['tables']);

        $bindings = $keys = $values = array();

        foreach ($data as $key => $value) {
            $keys[] = $key;
            if ($value instanceof Raw) {
                $values[] = (string) $value;
            } else {
                $values[] =  '?';
                $bindings[] = $value;
            }
        }

        $sqlArray = array(
            $type . ' INTO',
            $this->wrapSanitizer($table),
            '(' . $this->arrayStr($keys, ',') . ')',
            'VALUES',
            '(' . $this->arrayStr($values, ',', false) . ')',
        );

        if (isset($statements['onduplicate'])) {
            if (count($statements['onduplicate']) < 1) {
                throw new Exception('No data given.', 4);
            }
            list($updateStatement, $updateBindings) = $this->getUpdateStatement($statements['onduplicate']);
            $sqlArray[] = 'ON DUPLICATE KEY UPDATE ' . $updateStatement;
            $bindings = array_merge($bindings, $updateBindings);
        }

        $sql = $this->concatenateQuery($sqlArray, ' ', false);

        return compact('sql', 'bindings');
    }

    /**
     * Build Insert query
     *
     * @param       $statements
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function insert($statements, array $data)
    {
        return $this->doInsert($statements, $data, 'INSERT');
    }

    /**
     * Build Insert Ignore query
     *
     * @param       $statements
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function insertIgnore($statements, array $data)
    {
        return $this->doInsert($statements, $data, 'INSERT IGNORE');
    }

    /**
     * Build Insert Ignore query
     *
     * @param       $statements
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function replace($statements, array $data)
    {
        return $this->doInsert($statements, $data, 'REPLACE');
    }

    /**
     * Build fields assignment part of SET ... or ON DUBLICATE KEY UPDATE ... statements
     *
     * @param array $data
     *
     * @return array
     */
    private function getUpdateStatement($data)
    {
        $bindings = array();
        $statement = '';

        foreach ($data as $key => $value) {
            if ($value instanceof Raw) {
                $statement .= $this->wrapSanitizer($key) . '=' . $value . ',';
            } else {
                $statement .= $this->wrapSanitizer($key) . '=?,';
                $bindings[] = $value;
            }
        }

        $statement = trim($statement, ',');
        return array($statement, $bindings);
    }

    /**
     * Build update query
     *
     * @param       $statements
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function update($statements, array $data)
    {
        if (!isset($statements['tables'])) {
            throw new Exception('No table specified', 3);
        } elseif (count($data) < 1) {
            throw new Exception('No data given.', 4);
        }

        $table = end($statements['tables']);

        // Update statement
        list($updateStatement, $bindings) = $this->getUpdateStatement($data);

        // Wheres
        list($whereCriteria, $whereBindings) = $this->buildCriteriaWithType($statements, 'wheres', 'WHERE');

        // Limit
        $limit = isset($statements['limit']) ? 'LIMIT ' . $statements['limit'] : '';

        $sqlArray = array(
            'UPDATE',
            $this->wrapSanitizer($table),
            'SET ' . $updateStatement,
            $whereCriteria,
            $limit
        );

        $sql = $this->concatenateQuery($sqlArray, ' ', false);

        $bindings = array_merge($bindings, $whereBindings);
        return compact('sql', 'bindings');
    }

    /**
     * Build delete query
     *
     * @param $statements
     *
     * @return array
     * @throws Exception
     */
    public function delete($statements)
    {
        if (!isset($statements['tables'])) {
            throw new Exception('No table specified', 3);
        }

        $table = end($statements['tables']);

        // Wheres
        list($whereCriteria, $whereBindings) = $this->buildCriteriaWithType($statements, 'wheres', 'WHERE');

        // Limit
        $limit = isset($statements['limit']) ? 'LIMIT ' . $statements['limit'] : '';

        $sqlArray = array('DELETE FROM', $this->wrapSanitizer($table), $whereCriteria);
        $sql = $this->concatenateQuery($sqlArray, ' ', false);
        $bindings = $whereBindings;

        return compact('sql', 'bindings');
    }

    /**
     * Array concatenating method, like implode.
     * But it does wrap sanitizer and trims last glue
     *
     * @param array $pieces
     * @param       $glue
     * @param bool  $wrapSanitizer
     *
     * @return string
     */
    protected function arrayStr(array $pieces, $glue, $wrapSanitizer = true)
    {
        $str = '';
        foreach ($pieces as $key => $piece) {
            if ($wrapSanitizer) {
                $piece = $this->wrapSanitizer($piece);
            }

            if (!is_int($key)) {
                $piece = ($wrapSanitizer ? $this->wrapSanitizer($key) : $key) . ' AS ' . $piece;
            }

            $str .= $piece . $glue;
        }

        return trim($str, $glue);
    }

    /**
     * Join different part of queries with a space.
     *
     * @param array $pieces
     *
     * @return string
     */
    protected function concatenateQuery(array $pieces)
    {
        $str = '';
        foreach ($pieces as $piece) {
            $str = trim($str) . ' ' . trim($piece);
        }
        return trim($str);
    }

    /**
     * Build generic criteria string and bindings from statements, like "a = b and c = ?"
     *
     * @param      $statements
     * @param bool $bindValues
     *
     * @return array
     */
    protected function buildCriteria($statements, $bindValues = true)
    {
        $criteria = '';
        $bindings = array();
        foreach ($statements as $statement) {
            $key = $this->wrapSanitizer($statement['key']);
            $value = $statement['value'];

            if (is_null($value) && $key instanceof \Closure) {
                // We have a closure, a nested criteria

                // Build a new NestedCriteria class, keep it by reference so any changes made
                // in the closure should reflect here
                $nestedCriteria = $this->container->build(
                    '\\Pixie\\QueryBuilder\\NestedCriteria',
                    array($this->connection)
                );

                $nestedCriteria = & $nestedCriteria;
                // Call the closure with our new nestedCriteria object
                $key($nestedCriteria);
                // Get the criteria only query from the nestedCriteria object
                $queryObject = $nestedCriteria->getQuery('criteriaOnly', true);
                // Merge the bindings we get from nestedCriteria object
                $bindings = array_merge($bindings, $queryObject->getBindings());
                // Append the sql we get from the nestedCriteria object
                $criteria .= $statement['joiner'] . ' (' . $queryObject->getSql() . ') ';
            } elseif (is_array($value)) {
                // where_in or between like query
                $criteria .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'];

                switch ($statement['operator']) {
                    case 'BETWEEN':
                        $bindings = array_merge($bindings, $statement['value']);
                        $criteria .= ' ? AND ? ';
                        break;
                    default:
                        $valuePlaceholder = '';
                        foreach ($statement['value'] as $subValue) {
                            $valuePlaceholder .= '?, ';
                            $bindings[] = $subValue;
                        }

                        $valuePlaceholder = trim($valuePlaceholder, ', ');
                        $criteria .= ' (' . $valuePlaceholder . ') ';
                        break;
                }
            } elseif ($value instanceof Raw) {
                $criteria .= "{$statement['joiner']} {$key} {$statement['operator']} $value ";
            } else {
                // Usual where like criteria

                if (!$bindValues) {
                    // Specially for joins

                    // We are not binding values, lets sanitize then
                    $value = $this->wrapSanitizer($value);
                    $criteria .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'] . ' ' . $value . ' ';
                } elseif ($statement['key'] instanceof Raw) {
                    $criteria .= $statement['joiner'] . ' ' . $key . ' ';
                    $bindings = array_merge($bindings, $statement['key']->getBindings());
                } else {
                    // For wheres

                    $valuePlaceholder = '?';
                    $bindings[] = $value;
                    $criteria .= $statement['joiner'] . ' ' . $key . ' ' . $statement['operator'] . ' '
                        . $valuePlaceholder . ' ';
                }
            }
        }

        // Clear all white spaces, and, or from beginning and white spaces from ending
        $criteria = preg_replace('/^(\s?AND ?|\s?OR ?)|\s$/i', '', $criteria);

        return array($criteria, $bindings);
    }

    /**
     * Wrap values with adapter's sanitizer like, '`'
     *
     * @param $value
     *
     * @return string
     */
    public function wrapSanitizer($value)
    {
        // Its a raw query, just cast as string, object has __toString()
        if ($value instanceof Raw) {
            return (string)$value;
        } elseif ($value instanceof \Closure) {
            return $value;
        }

        // Separate our table and fields which are joined with a ".",
        // like my_table.id
        $valueArr = explode('.', $value, 2);

        foreach ($valueArr as $key => $subValue) {
            // Don't wrap if we have *, which is not a usual field
            $valueArr[$key] = trim($subValue) == '*' ? $subValue : $this->sanitizer . $subValue . $this->sanitizer;
        }

        // Join these back with "." and return
        return implode('.', $valueArr);
    }

    /**
     * Build criteria string and binding with various types added, like WHERE and Having
     *
     * @param      $statements
     * @param      $key
     * @param      $type
     * @param bool $bindValues
     *
     * @return array
     */
    protected function buildCriteriaWithType($statements, $key, $type, $bindValues = true)
    {
        $criteria = '';
        $bindings = array();

        if (isset($statements[$key])) {
            // Get the generic/adapter agnostic criteria string from parent
            list($criteria, $bindings) = $this->buildCriteria($statements[$key], $bindValues);

            if ($criteria) {
                $criteria = $type . ' ' . $criteria;
            }
        }

        return array($criteria, $bindings);
    }

    /**
     * Build join string
     *
     * @param $statements
     *
     * @return array
     */
    protected function buildJoin($statements)
    {
        $sql = '';

        if (!array_key_exists('joins', $statements) || !is_array($statements['joins'])) {
            return $sql;
        }

        foreach ($statements['joins'] as $joinArr) {
            if (is_array($joinArr['table'])) {
                $mainTable = $joinArr['table'][0];
                $aliasTable = $joinArr['table'][1];
                $table = $this->wrapSanitizer($mainTable) . ' AS ' . $this->wrapSanitizer($aliasTable);
            } else {
                $table = $joinArr['table'] instanceof Raw ?
                    (string) $joinArr['table'] :
                    $this->wrapSanitizer($joinArr['table']);
            }
            $joinBuilder = $joinArr['joinBuilder'];

            $sqlArr = array(
                $sql,
                strtoupper($joinArr['type']),
                'JOIN',
                $table,
                'ON',
                $joinBuilder->getQuery('criteriaOnly', false)->getSql()
            );

            $sql = $this->concatenateQuery($sqlArr);
        }

        return $sql;
    }
}
