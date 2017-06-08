<?php namespace Pixie;

use Mockery as m;
class ConnectionTest extends TestCase
{
    private $mysqlConnectionMock;
    private $connection;

    public function setUp()
    {
        parent::setUp();

        $this->mysqlConnectionMock = m::mock('\\Pixie\\ConnectionAdapters\\Mysql');
        $this->mysqlConnectionMock->shouldReceive('connect')->andReturn($this->mockPdo);

        $this->container->setInstance('\\Pixie\\ConnectionAdapters\\Mysqlmock', $this->mysqlConnectionMock);
        $this->connection = new Connection('mysqlmock', array('prefix' => 'cb_'), null, $this->container);
    }

    public function testConnection()
    {
        $this->assertEquals($this->mockPdo, $this->connection->getPdoInstance());
        $this->assertInstanceOf('\\PDO', $this->connection->getPdoInstance());
        $this->assertEquals('mysqlmock', $this->connection->getAdapter());
        $this->assertEquals(array('prefix' => 'cb_'), $this->connection->getAdapterConfig());
    }

    public function testQueryBuilderAliasCreatedByConnection()
    {
        $mockQBAdapter = m::mock('\\Pixie\\QueryBuilder\\Adapters\\Mysql');

        $this->container->setInstance('\\Pixie\\QueryBuilder\\Adapters\\Mysqlmock', $mockQBAdapter);
        $connection = new Connection('mysqlmock', array('prefix' => 'cb_'), 'DBAlias', $this->container);
        $this->assertEquals($this->mockPdo, $connection->getPdoInstance());
        $this->assertInstanceOf('\\Pixie\\QueryBuilder\\QueryBuilderHandler', \DBAlias::newQuery());
    }
}