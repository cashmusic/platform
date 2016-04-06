<?php
use PayPal\Core\PPLoggingManager;
/**
 * Test class for PPLoggingManager.
 *
 */
class PPLoggingManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PPLoggingManager
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PPLoggingManager('InvoiceTest');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @test
     */
    public function testError()
    {
    	 $this->object->error('Test Error Message');

    }

    /**
     * @test
     */
    public function testWarning()
    {
         $this->object->warning('Test Warning Message');
    }

    /**
     * @test
     */
    public function testInfo()
    {
        $this->object->info('Test info Message');
    }

    /**
     * @test
     */
    public function testFine()
    {
       $this->object->fine('Test fine Message');
    }
}
?>
