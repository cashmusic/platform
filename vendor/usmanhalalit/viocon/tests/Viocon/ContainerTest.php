<?php namespace Viocon;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    protected static $container;

    public static function setupBeforeClass()
    {
        static::$container = new Container();
    }

    public function testBuildWithClassNameAsKey()
    {
        $stdClass = static::$container->build('\\stdClass');
        $this->assertInstanceOf('\\stdClass', $stdClass);
    }

    public function testBuildWithClassNameAsKeyWithConstructorParams()
    {
        $reflectionClass = static::$container->build('\\ReflectionClass', array('\\Viocon\ContainerTest'));
        $this->assertInstanceOf('\\ReflectionClass', $reflectionClass);
        $this->assertEquals('Viocon\ContainerTest', $reflectionClass->name);
    }

    public function testSetAndBuild()
    {
        static::$container->set('myStdClass', '\\stdClass');
        $stdClass = static::$container->build('myStdClass');
        $this->assertInstanceOf('\\stdClass', $stdClass);
    }

    public function testSetAndBuildClosure()
    {
        static::$container->set(
            'myClosure',
            function ($test1, $test2) {
                $stdClass = new \stdClass();
                $stdClass->testVar1 = $test1;
                $stdClass->testVar2 = $test2;
                $stdClass->testMethod = function ($test3) {
                    return $test3;
                };

                return $stdClass;
            }
        );

        $myClosure = static::$container->build('myClosure', array('Test Var 1', 'Test Var 2'));
        $this->assertInstanceOf('\\stdClass', $myClosure);
        $this->assertEquals('Test Var 1', $myClosure->testVar1);
        $this->assertEquals('Test Var 2', $myClosure->testVar2);
        $method = $myClosure->testMethod;
        $this->assertEquals('test', $method('test'));
    }

    public function testReplacingWithMockedInstanceOnRuntime()
    {
        $mockedClass = new \stdClass();
        $mockedClass->testVar = 'mocked';
        static::$container->setInstance('\\stdClass', $mockedClass);

        $stdClass = static::$container->build('\\stdClass');
        $this->assertEquals('mocked', $stdClass->testVar);
    }

    public function testSingleton()
    {
        $container = static::$container;
        $container->singleton('mySingleton', '\\stdClass');
        $this->assertInstanceOf('\\stdClass', $container->build('mySingleton'));
    }

    public function testSingletonWithPersistence()
    {
        $container = new Container();
        $container->singleton('mySingleton', '\\stdClass');
        $stdClass = $container->build('mySingleton');
        $stdClass->testVar = 'value';

        $stdClass2 = $container->build('mySingleton');
        $this->assertEquals('value', $stdClass2->testVar);
    }

    public function testAlias()
    {
        new Container('VContainer');
        \VContainer::set('myClosure', function()
        {
            return 'Test';
        });

        $this->assertEquals('Test', \VContainer::build('myClosure'));
    }
}