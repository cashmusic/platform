<?php namespace Pixie;

use Mockery as m;

class QueryBuilderTest extends TestCase
{
    private $builder;

    public function setUp()
    {
        parent::setUp();

        $this->builder = new QueryBuilder\QueryBuilderHandler($this->mockConnection);
    }

    public function testSelectFlexibility()
    {
        $query = $this->builder
            ->select('foo')
            ->select(array('bar', 'baz'))
            ->select('qux', 'lol', 'wut')
            ->from('t');
        $this->assertEquals(
            'SELECT `foo`, `bar`, `baz`, `qux`, `lol`, `wut` FROM `cb_t`',
            $query->getQuery()->getRawSql(),
            'SELECT is pretty flexible!'
        );
    }

    public function testSelectQuery()
    {
        $subQuery = $this->builder->table('person_details')->select('details')->where('person_id', '=', 3);

        $query = $this->builder->table('my_table')
            ->select('my_table.*')
            ->select(array($this->builder->raw('count(cb_my_table.id) as tot'), $this->builder->subQuery($subQuery, 'pop')))
            ->where('value', '=', 'Ifrah')
            ->whereNot('my_table.id', -1)
            ->orWhereNot('my_table.id', -2)
            ->orWhereIn('my_table.id', array(1, 2))
            ->groupBy(array('value', 'my_table.id', 'person_details.id'))
            ->orderBy('my_table.id', 'DESC')
            ->orderBy('value')
            ->having('tot', '<', 2)
            ->limit(1)
            ->offset(0)
            ->join(
                'person_details',
                'person_details.person_id',
                '=',
                'my_table.id'
            )
        ;

        $nestedQuery = $this->builder->table($this->builder->subQuery($query, 'bb'))->select('*');
        $this->assertEquals("SELECT * FROM (SELECT `cb_my_table`.*, count(cb_my_table.id) as tot, (SELECT `details` FROM `cb_person_details` WHERE `person_id` = 3) as pop FROM `cb_my_table` INNER JOIN `cb_person_details` ON `cb_person_details`.`person_id` = `cb_my_table`.`id` WHERE `value` = 'Ifrah' AND NOT `cb_my_table`.`id` = -1 OR NOT `cb_my_table`.`id` = -2 OR `cb_my_table`.`id` IN (1, 2) GROUP BY `value`, `cb_my_table`.`id`, `cb_person_details`.`id` HAVING `tot` < 2 ORDER BY `cb_my_table`.`id` DESC, `value` ASC LIMIT 1 OFFSET 0) as bb"
            , $nestedQuery->getQuery()->getRawSql());
    }

    public function testSelectAliases()
    {
        $query = $this->builder->from('my_table')->select('foo')->select(array('bar' => 'baz', 'qux'));

        $this->assertEquals(
            "SELECT `foo`, `bar` AS `baz`, `qux` FROM `cb_my_table`",
            $query->getQuery()->getRawSql()
        );
    }

    public function testRawStatementsWithinCriteria()
    {
        $query = $this->builder->from('my_table')
            ->where('simple', 'criteria')
            ->where($this->builder->raw('RAW'))
            ->where($this->builder->raw('PARAMETERIZED_ONE(?)', 'foo'))
            ->where($this->builder->raw('PARAMETERIZED_SEVERAL(?, ?, ?)', array(1, '2', 'foo')));

        $this->assertEquals(
            "SELECT * FROM `cb_my_table` WHERE `simple` = 'criteria' AND RAW AND PARAMETERIZED_ONE('foo') AND PARAMETERIZED_SEVERAL(1, '2', 'foo')",
            $query->getQuery()->getRawSql()
        );
    }

    public function testStandaloneWhereNot()
    {
        $query = $this->builder->table('my_table')->whereNot('foo', 1);
        $this->assertEquals("SELECT * FROM `cb_my_table` WHERE NOT `foo` = 1", $query->getQuery()->getRawSql());
    }

    public function testSelectDistinct()
    {
        $query = $this->builder->selectDistinct(array('name', 'surname'))->from('my_table');
        $this->assertEquals("SELECT DISTINCT `name`, `surname` FROM `cb_my_table`", $query->getQuery()->getRawSql());
    }

    public function testSelectDistinctWithSingleColumn()
    {
        $query = $this->builder->selectDistinct('name')->from('my_table');
        $this->assertEquals("SELECT DISTINCT `name` FROM `cb_my_table`", $query->getQuery()->getRawSql());
    }

    public function testSelectDistinctAndSelectCalls()
    {
        $query = $this->builder->select('name')->selectDistinct('surname')->select(array('birthday', 'address'))->from('my_table');
        $this->assertEquals("SELECT DISTINCT `name`, `surname`, `birthday`, `address` FROM `cb_my_table`", $query->getQuery()->getRawSql());
    }

    public function testSelectQueryWithNestedCriteriaAndJoins()
    {
        $builder = $this->builder;

        $query = $builder->table('my_table')
            ->where('my_table.id', '>', 1)
            ->orWhere('my_table.id', 1)
            ->where(function($q)
                {
                    $q->where('value', 'LIKE', '%sana%');
                    $q->orWhere(function($q2)
                        {
                            $q2->where('key', 'LIKE', '%sana%');
                            $q2->orWhere('value', 'LIKE', '%sana%');
                        });
                })
            ->join(array('person_details', 'a'), 'a.person_id', '=', 'my_table.id')

            ->leftJoin(array('person_details', 'b'), function($table) use ($builder)
                {
                    $table->on('b.person_id', '=', 'my_table.id');
                    $table->on('b.deleted', '=', $builder->raw(0));
                    $table->orOn('b.age', '>', $builder->raw(1));
                })
        ;

        $this->assertEquals("SELECT * FROM `cb_my_table` INNER JOIN `cb_person_details` AS `cb_a` ON `cb_a`.`person_id` = `cb_my_table`.`id` LEFT JOIN `cb_person_details` AS `cb_b` ON `cb_b`.`person_id` = `cb_my_table`.`id` AND `cb_b`.`deleted` = 0 OR `cb_b`.`age` > 1 WHERE `cb_my_table`.`id` > 1 OR `cb_my_table`.`id` = 1 AND (`value` LIKE '%sana%' OR (`key` LIKE '%sana%' OR `value` LIKE '%sana%'))"
            , $query->getQuery()->getRawSql());
    }

    public function testSelectWithQueryEvents()
    {
        $builder = $this->builder;

        $builder->registerEvent('before-select', ':any', function($qb)
        {
            $qb->whereIn('status', array(1, 2));
        });

        $query = $builder->table('some_table')->where('name', 'Some');
        $query->get();
        $actual = $query->getQuery()->getRawSql();

        $this->assertEquals("SELECT * FROM `cb_some_table` WHERE `name` = 'Some' AND `status` IN (1, 2)", $actual);
    }

    public function testEventPropagation()
    {
        $builder = $this->builder;
        $counter = 0;

        foreach (array('before', 'after') as $prefix) {
            foreach (array('insert', 'select', 'update', 'delete') as $action) {
                $builder->registerEvent("$prefix-$action", ':any', function ($qb) use (&$counter) {
                    return $counter++;
                });
            }
        }

        $insert = $builder->table('foo')->insert(array('bar' => 'baz'));
        $this->assertEquals(0, $insert);
        $this->assertEquals(1, $counter, 'after-insert was not called');

        $select = $builder->from('foo')->select('bar')->get();
        $this->assertEquals(1, $select);
        $this->assertEquals(2, $counter, 'after-select was not called');

        $update = $builder->table('foo')->update(array('bar' => 'baz'));
        $this->assertEquals(2, $update);
        $this->assertEquals(3, $counter, 'after-update was not called');

        $delete = $builder->from('foo')->delete();
        $this->assertEquals(3, $delete);
        $this->assertEquals(4, $counter, 'after-delete was not called');
    }

    public function testInsertQuery()
    {
        $builder = $this->builder->from('my_table');
        $data = array('key' => 'Name',
                'value' => 'Sana',);

        $this->assertEquals("INSERT INTO `cb_my_table` (`key`,`value`) VALUES ('Name','Sana')"
            , $builder->getQuery('insert', $data)->getRawSql());
    }

    public function testInsertIgnoreQuery()
    {
        $builder = $this->builder->from('my_table');
        $data = array('key' => 'Name',
            'value' => 'Sana',);

        $this->assertEquals("INSERT IGNORE INTO `cb_my_table` (`key`,`value`) VALUES ('Name','Sana')"
            , $builder->getQuery('insertignore', $data)->getRawSql());
    }

    public function testReplaceQuery()
    {
        $builder = $this->builder->from('my_table');
        $data = array('key' => 'Name',
            'value' => 'Sana',);

        $this->assertEquals("REPLACE INTO `cb_my_table` (`key`,`value`) VALUES ('Name','Sana')"
            , $builder->getQuery('replace', $data)->getRawSql());
    }

    public function testInsertOnDuplicateKeyUpdateQuery()
    {
        $builder = $this->builder;
        $data = array(
            'name' => 'Sana',
            'counter' => 1
        );
        $dataUpdate = array(
            'name' => 'Sana',
            'counter' => 2
        );
        $builder->from('my_table')->onDuplicateKeyUpdate($dataUpdate);
        $this->assertEquals("INSERT INTO `cb_my_table` (`name`,`counter`) VALUES ('Sana',1) ON DUPLICATE KEY UPDATE `name`='Sana',`counter`=2"
            , $builder->getQuery('insert', $data)->getRawSql());
    }

    public function testUpdateQuery()
    {
        $builder = $this->builder->table('my_table')->where('value', 'Sana');

        $data = array(
            'key' => 'Sana',
            'value' => 'Amrin',
        );

        $this->assertEquals("UPDATE `cb_my_table` SET `key`='Sana',`value`='Amrin' WHERE `value` = 'Sana'"
            , $builder->getQuery('update', $data)->getRawSql());
    }

    public function testDeleteQuery()
    {
        $this->builder = new QueryBuilder\QueryBuilderHandler($this->mockConnection);

        $builder = $this->builder->table('my_table')->where('value', '=', 'Amrin');

        $this->assertEquals("DELETE FROM `cb_my_table` WHERE `value` = 'Amrin'"
            , $builder->getQuery('delete')->getRawSql());
    }

    public function testOrderByFlexibility()
    {
        $query = $this->builder
            ->from('t')
            ->orderBy('foo', 'DESC')
            ->orderBy(array('bar', 'baz' => 'ASC', $this->builder->raw('raw1')), 'DESC')
            ->orderBy($this->builder->raw('raw2'), 'DESC');

        $this->assertEquals(
            'SELECT * FROM `cb_t` ORDER BY `foo` DESC, `bar` DESC, `baz` ASC, raw1 DESC, raw2 DESC',
            $query->getQuery()->getRawSql(),
            'ORDER BY is flexible enough!'
        );
    }

    public function testSelectQueryWithNull()
    {
        $query = $this->builder->from('my_table')
                ->whereNull('key1')
                ->orWhereNull('key2')
                ->whereNotNull('key3')
                ->orWhereNotNull('key4');

        $this->assertEquals(
            "SELECT * FROM `cb_my_table` WHERE `key1` IS  NULL OR `key2` IS  NULL AND `key3` IS NOT NULL OR `key4` IS NOT NULL",
            $query->getQuery()->getRawSql()
        );
    }

    public function testIsPossibleToUseSubqueryInWhereClause()
    {
        $sub = clone $this->builder;
        $query = $this->builder->from('my_table')->whereIn('foo', $this->builder->subQuery(
            $sub->from('some_table')->select('foo')->where('id', 1)
        ));
        $this->assertEquals(
            "SELECT * FROM `cb_my_table` WHERE `foo` IN (SELECT `foo` FROM `cb_some_table` WHERE `id` = 1)",
            $query->getQuery()->getRawSql()
        );
    }

    public function testIsPossibleToUseSubqueryInWhereNotClause()
    {
        $sub = clone $this->builder;
        $query = $this->builder->from('my_table')->whereNotIn('foo', $this->builder->subQuery(
            $sub->from('some_table')->select('foo')->where('id', 1)
        ));
        $this->assertEquals(
            "SELECT * FROM `cb_my_table` WHERE `foo` NOT IN (SELECT `foo` FROM `cb_some_table` WHERE `id` = 1)",
            $query->getQuery()->getRawSql()
        );
    }
}
