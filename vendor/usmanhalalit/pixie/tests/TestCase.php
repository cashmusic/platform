<?php namespace Pixie;

use Mockery as m;
use Viocon\Container;

class TestCase extends \PHPUnit_Framework_TestCase {
    /**
     * @var Container
     */
    protected $container;
    protected $mockConnection;
    protected $mockPdo;
    protected $mockPdoStatement;

    public function setUp()
    {
        $this->container = new Container();

        $this->mockPdoStatement = $this->getMock('\\PDOStatement');

        $mockPdoStatement = & $this->mockPdoStatement;

        $mockPdoStatement->bindings = array();

        $this->mockPdoStatement
            ->expects($this->any())
            ->method('bindValue')
            ->will($this->returnCallback(function ($parameter, $value, $dataType) use ($mockPdoStatement) {
                $mockPdoStatement->bindings[] = array($value, $dataType);
            }));

        $this->mockPdoStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function($bindings = null) use ($mockPdoStatement) {
                if ($bindings) {
                    $mockPdoStatement->bindings = $bindings;
                }
            }));


        $this->mockPdoStatement
            ->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnCallback(function() use ($mockPdoStatement){
                return array($mockPdoStatement->sql, $mockPdoStatement->bindings);
            }));

        $this->mockPdo = $this->getMock('\\Pixie\\MockPdo', array('prepare', 'setAttribute', 'quote', 'lastInsertId'));

        $this->mockPdo
            ->expects($this->any())
            ->method('prepare')
            ->will($this->returnCallback(function($sql) use ($mockPdoStatement){
                $mockPdoStatement->sql = $sql;
                return $mockPdoStatement;
            }));

        $this->mockPdo
            ->expects($this->any())
            ->method('quote')
            ->will($this->returnCallback(function($value){
                return "'$value'";
            }));

        $eventHandler = new EventHandler();

        $this->mockConnection = m::mock('\\Pixie\\Connection');
        $this->mockConnection->shouldReceive('getPdoInstance')->andReturn($this->mockPdo);
        $this->mockConnection->shouldReceive('getAdapter')->andReturn('mysql');
        $this->mockConnection->shouldReceive('getAdapterConfig')->andReturn(array('prefix' => 'cb_'));
        $this->mockConnection->shouldReceive('getContainer')->andReturn($this->container);
        $this->mockConnection->shouldReceive('getEventHandler')->andReturn($eventHandler);
    }

    public function tearDown()
    {
        m::close();
    }

    public function callbackMock()
    {
        $args = func_get_args();
        return count($args) == 1 ? $args[0] : $args;
    }
}

class MockPdo extends \PDO
{
    public function __construct()
    {

    }
}