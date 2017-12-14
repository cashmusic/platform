<?php namespace Pixie;

use PDO;
use Mockery as m;
use Pixie\QueryBuilder\QueryBuilderHandler;

class QueryBuilder extends TestCase
{
    /**
     * @var QueryBuilderHandler
     */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->builder = new QueryBuilderHandler($this->mockConnection);
    }

    public function testRawQuery()
    {
        $query = 'select * from cb_my_table where id = ? and name = ?';
        $bindings = array(5, 'usman');
        $queryArr = $this->builder->query($query, $bindings)->get();
        $this->assertEquals(
            array(
                $query,
                array(array(5, PDO::PARAM_INT), array('usman', PDO::PARAM_STR)),
            ),
            $queryArr
        );
    }

    public function testInsertQueryReturnsIdForInsert()
    {
        $this->mockPdoStatement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->mockPdo
            ->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(11));

        $id = $this->builder->table('test')->insert(array(
            'id' => 5,
            'name' => 'usman'
        ));

        $this->assertEquals(11, $id);
    }

    public function testInsertQueryReturnsIdForInsertIgnore()
    {
        $this->mockPdoStatement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));

        $this->mockPdo
            ->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(11));

        $id = $this->builder->table('test')->insertIgnore(array(
            'id' => 5,
            'name' => 'usman'
        ));

        $this->assertEquals(11, $id);
    }

    public function testInsertQueryReturnsNullForIgnoredInsert()
    {
        $this->mockPdoStatement
            ->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(0));

        $id = $this->builder->table('test')->insertIgnore(array(
            'id' => 5,
            'name' => 'usman'
        ));

        $this->assertEquals(null, $id);
    }

    /**
     * @expectedException \Pixie\Exception
     * @expectedExceptionCode 3
     */
    public function testTableNotSpecifiedException(){
        $this->builder->where('a', 'b')->get();
    }
}