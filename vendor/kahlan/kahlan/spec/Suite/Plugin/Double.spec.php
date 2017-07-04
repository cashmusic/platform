<?php
namespace Kahlan\Kahlan\Spec\Suite\Plugin;

use Exception;
use ReflectionMethod;
use InvalidArgumentException;
use DateTime;

use Kahlan\Jit\Interceptor;
use Kahlan\Jit\Patchers;
use Kahlan\Arg;
use Kahlan\Jit\Patcher\Pointcut as PointcutPatcher;
use Kahlan\Jit\Patcher\Monkey as MonkeyPatcher;
use Kahlan\Plugin\Double;

use Kahlan\Spec\Fixture\Plugin\Monkey\User;
use Kahlan\Spec\Fixture\Plugin\Pointcut\Foo;
use Kahlan\Spec\Fixture\Plugin\Pointcut\SubBar;

describe("Double", function () {

    /**
     * Save current & reinitialize the Interceptor class.
     */
    beforeAll(function () {
        $this->previous = Interceptor::instance();
        Interceptor::unpatch();

        $cachePath = rtrim(sys_get_temp_dir(), DS) . DS . 'kahlan';
        $include = ['Kahlan\Spec\\'];
        $interceptor = Interceptor::patch(compact('include', 'cachePath'));
        $interceptor->patchers()->add('pointcut', new PointcutPatcher());
        $interceptor->patchers()->add('monkey', new MonkeyPatcher());
    });

    /**
     * Restore Interceptor class.
     */
    afterAll(function () {
        Interceptor::load($this->previous);
    });

    describe("::_generateAbstractMethods()", function () {

        it("throws an exception when called with a non-existing class", function () {

            expect(function () {
                $double = Double::classname([
                    'extends' => 'Kahlan\Plugin\Double',
                    'methods' => ['::generateAbstractMethods']
                ]);
                allow($double)->toReceive('::generateAbstractMethods')->andRun(function ($class) {
                    return static::_generateAbstractMethods($class);
                });
                $double::generateAbstractMethods('some\unexisting\Class');
            })->toThrow();

        });

    });

    describe("::create()", function () {

        beforeAll(function () {
            $this->is_method_exists = function ($instance, $method, $type = "public") {
                if (!method_exists($instance, $method)) {
                    return false;
                }
                $refl = new ReflectionMethod($instance, $method);
                switch ($type) {
                    case "static":
                        return $refl->isStatic();
                    break;
                    case "public":
                        return $refl->isPublic();
                    break;
                    case "private":
                        return $refl->isPrivate();
                    break;
                }
                return false;
            };
        });

        it("stubs an instance", function () {

            $double = Double::instance();
            expect(is_object($double))->toBe(true);
            expect(get_class($double))->toMatch("/^Kahlan\\\Spec\\\Plugin\\\Double\\\Double\d+$/");

        });

        it("names a stub instance", function () {

            $double = Double::instance(['class' => 'Kahlan\Spec\Double\MyDouble']);
            expect(is_object($double))->toBe(true);
            expect(get_class($double))->toBe('Kahlan\Spec\Double\MyDouble');

        });

        it("stubs an instance with a parent class", function () {

            $double = Double::instance(['extends' => 'Kahlan\Util\Text']);
            expect(is_object($double))->toBe(true);
            expect(get_parent_class($double))->toBe('Kahlan\Util\Text');

        });

        it("stubs an instance using a trait", function () {

            $double = Double::instance(['uses' => 'Kahlan\Spec\Mock\Plugin\Double\HelloTrait']);
            expect($double->hello())->toBe('Hello World From Trait!');

        });

        it("stubs an instance implementing some interface", function () {

            $double = Double::instance(['implements' => ['ArrayAccess', 'Iterator']]);
            $interfaces = class_implements($double);
            expect(isset($interfaces['ArrayAccess']))->toBe(true);
            expect(isset($interfaces['Iterator']))->toBe(true);
            expect(isset($interfaces['Traversable']))->toBe(true);

        });

        it("stubs an instance with multiple stubbed methods", function () {

            $double = Double::instance();
            allow($double)->toReceive('message')->andReturn('Good Evening World!', 'Good Bye World!');
            allow($double)->toReceive('bar')->andReturn('Hello Bar!');

            expect($double->message())->toBe('Good Evening World!');
            expect($double->message())->toBe('Good Bye World!');
            expect($double->bar())->toBe('Hello Bar!');

        });

        it("stubs static methods on a stub instance", function () {

            $double = Double::instance();
            allow($double)->toReceive('::magicCallStatic')->andReturn('Good Evening World!', 'Good Bye World!');

            expect($double::magicCallStatic())->toBe('Good Evening World!');
            expect($double::magicCallStatic())->toBe('Good Bye World!');

        });

        it("produces unique instance", function () {

            $double = Double::instance();
            $double2 = Double::instance();

            expect(get_class($double))->not->toBe(get_class($double2));

        });

        it("stubs instances with some magic methods if no parent defined", function () {

            $double = Double::instance();

            expect($double)->toReceive('__get')->ordered;
            expect($double)->toReceive('__set')->ordered;
            expect($double)->toReceive('__isset')->ordered;
            expect($double)->toReceive('__unset')->ordered;
            expect($double)->toReceive('__sleep')->ordered;
            expect($double)->toReceive('__toString')->ordered;
            expect($double)->toReceive('__invoke')->ordered;
            expect(get_class($double))->toReceive('__wakeup')->ordered;
            expect(get_class($double))->toReceive('__clone')->ordered;

            $prop = $double->prop;
            $double->prop = $prop;
            expect(isset($double->prop))->toBe(true);
            expect(isset($double->data))->toBe(false);
            unset($double->data);
            $serialized = serialize($double);
            $string = (string) $double;
            $double();
            unserialize($serialized);
            $double2 = clone $double;

        });

        it("defaults stub can be used as container", function () {

            $double = Double::instance();
            $double->data = 'hello';
            expect($double->data)->toBe('hello');

        });

        it("stubs an instance with an extra method", function () {

            $double = Double::instance([
                'methods' => ['method1']
            ]);

            expect($this->is_method_exists($double, 'method1'))->toBe(true);
            expect($this->is_method_exists($double, 'method2'))->toBe(false);
            expect($this->is_method_exists($double, 'method1', 'static'))->toBe(false);

        });

        it("stubs an instance with an extra static method", function () {

            $double = Double::instance([
                'methods' => ['::method1']
            ]);

            expect($this->is_method_exists($double, 'method1'))->toBe(true);
            expect($this->is_method_exists($double, 'method2'))->toBe(false);
            expect($this->is_method_exists($double, 'method1', 'static'))->toBe(true);

        });

        it("stubs an instance with an extra method returning by reference", function () {

            $double = Double::instance([
                'methods' => ['&method1']
            ]);

            $double->method1();
            expect(method_exists($double, 'method1'))->toBe(true);

            $array = [];
            allow($double)->toReceive('method1')->andRun(function () use (&$array) {
                $array[] = 'in';
            });

            $result = $double->method1();
            $result[] = 'out';
            expect($array)->toBe(['in'/*, 'out'*/]); //I guess that's the limit of the system.

        });

        it("applies constructor parameters to the stub", function () {

            $double = Double::instance([
                'extends' => 'Kahlan\Spec\Fixture\Plugin\Double\ConstrDoz',
                'args'    => ['a', 'b']
            ]);

            expect($double->a)->toBe('a');
            expect($double->b)->toBe('b');

        });

        it("expects method called in the past to be uncalled", function () {

            $double = Double::instance();
            $double->message();
            expect($double)->not->toReceive('message');

        });

    });

    describe("::classname()", function () {

        it("stubs class", function () {

            $double = Double::classname();
            expect($double)->toMatch("/^Kahlan\\\Spec\\\Plugin\\\Double\\\Double\d+$/");

        });

        it("names a stub class", function () {

            $double = Double::classname(['class' => 'Kahlan\Spec\Double\MyStaticDouble']);
            expect(is_string($double))->toBe(true);
            expect($double)->toBe('Kahlan\Spec\Double\MyStaticDouble');

        });

        it("stubs a stub class with multiple methods", function () {

            $classname = Double::classname();
            allow($classname)->toReceive('message')->andReturn('Good Evening World!', 'Good Bye World!');
            allow($classname)->toReceive('bar')->andReturn('Hello Bar!');

            $double = new $classname();
            expect($double->message())->toBe('Good Evening World!');

            $double2 = new $classname();
            expect($double->message())->toBe('Good Bye World!');

            $double3 = new $classname();
            expect($double->bar())->toBe('Hello Bar!');

        });

        it("stubs static methods on a stub class", function () {

            $classname = Double::classname();
            allow($classname)->toReceive('::magicCallStatic')->andReturn('Good Evening World!', 'Good Bye World!');

            expect($classname::magicCallStatic())->toBe('Good Evening World!');
            expect($classname::magicCallStatic())->toBe('Good Bye World!');

        });

        it("produces unique classname", function () {

            $double = Double::classname();
            $double2 = Double::classname();

            expect($double)->not->toBe($double2);

        });

        it("stubs classes with `construct()` if no parent defined", function () {

            $class = Double::classname();
            expect($class)->toReceive('__construct');
            $double = new $class();

        });

        it("expects method called in the past to be uncalled", function () {

            $class = Double::classname();
            $class::message();
            expect($class)->not->toReceive('::message');

        });

    });

    describe("::generate()", function () {

        it("throws an exception with an unexisting trait", function () {

            expect(function () {
                Double::generate(['uses' => ['an\unexisting\Trait']]);
            })->toThrow();

        });

        it("throws an exception with an unexisting interface", function () {

            expect(function () {
                Double::generate(['implements' => ['an\unexisting\Interface']]);
            })->toThrow();

        });

        it("throws an exception with an unexisting parent class", function () {

            expect(function () {
                Double::generate(['extends' => 'an\unexisting\ParentClass']);
            })->toThrow();

        });

        it("overrides the construct method", function () {

            $result = Double::generate([
                'class' => 'Kahlan\Spec\Plugin\Double\Double',
                'methods' => ['__construct'],
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double {

    public function __construct() {}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("generates use statement", function () {

            $result = Double::generate([
                'class'      => 'Kahlan\Spec\Plugin\Double\Double',
                'uses'       => ['Kahlan\Spec\Mock\Plugin\Double\HelloTrait'],
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double {

    use \\Kahlan\\Spec\\Mock\\Plugin\\Double\\HelloTrait;

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("generates abstract parent class methods", function () {

            $result = Double::generate([
                'class'      => 'Kahlan\Spec\Plugin\Double\Double',
                'extends'    => 'Kahlan\Spec\Fixture\Plugin\Double\AbstractDoz'
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double extends \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\AbstractDoz {

    public function foo(\$var) {}
    public function bar(\$var1 = NULL, array \$var2 = array()) {}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("generates interface methods", function () {

            $result = Double::generate([
                'class'        => 'Kahlan\Spec\Plugin\Double\Double',
                'implements'   => 'Countable',
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double implements \\Countable {

    public function count() {}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("generates interface methods for multiple insterfaces", function () {

            $result = Double::generate([
                'class'        => 'Kahlan\Spec\Plugin\Double\Double',
                'implements'   => ['Countable', 'SplObserver'],
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double implements \\Countable, \\SplObserver {

    public function count() {}
    public function update(\\SplSubject \$SplSubject) {}

}
?>
EOD;
            expect(str_replace('$subject', '$SplSubject', $result))->toBe($expected);

        });

        it("generates interface methods", function () {

            $result = Double::generate([
                'class'        => 'Kahlan\Spec\Plugin\Double\Double',
                'implements'   => null,
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double {



}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("generates interface methods with return type", function () {

            skipIf(PHP_MAJOR_VERSION < 7);

            $result = Double::generate([
                'class'        => 'Kahlan\Spec\Plugin\Double\Double',
                'implements'   => ['Kahlan\Spec\Fixture\Plugin\Double\ReturnTypesInterface'],
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double implements \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\ReturnTypesInterface {

    public function foo(array \$a) : bool {}
    public function bar() : \\Kahlan\\Spec\\Fixture\\Reporter\\Coverage\\ImplementsCoverageInterface {}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("generates interface methods with variadic variable", function () {

            skipIf(defined('HHVM_VERSION') || PHP_MAJOR_VERSION < 7);

            $result = Double::generate([
                'class'        => 'Kahlan\Spec\Plugin\Double\Double',
                'implements'   => ['Kahlan\Spec\Fixture\Plugin\Double\VariadicInterface'],
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double implements \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\VariadicInterface {

    public function foo(int ...\$integers) : int {}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("manages methods inheritence", function () {

            $result = Double::generate([
                'class'      => 'Kahlan\Spec\Plugin\Double\Double',
                'implements' => ['Kahlan\Spec\Fixture\Plugin\Double\DozInterface'],
                'magicMethods' => false
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double implements \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\DozInterface {

    public function foo(\$a) {}
    public function bar(\$b = NULL) {}

}
?>
EOD;
            expect($result)->toBe($expected);

            $result = Double::generate([
                'class'      => 'Kahlan\Spec\Plugin\Double\Double',
                'extends'    => 'Kahlan\Spec\Fixture\Plugin\Double\AbstractDoz',
                'implements' => ['Kahlan\Spec\Fixture\Plugin\Double\DozInterface'],
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double extends \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\AbstractDoz implements \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\DozInterface {

    public function foo(\$var) {}
    public function bar(\$var1 = NULL, array \$var2 = array()) {}

}
?>
EOD;
            expect($result)->toBe($expected);

            $result = Double::generate([
                'class'      => 'Kahlan\Spec\Plugin\Double\Double',
                'extends'    => 'Kahlan\Spec\Fixture\Plugin\Double\AbstractDoz',
                'implements' => ['Kahlan\Spec\Fixture\Plugin\Double\DozInterface'],
                'methods'    => ['foo', 'bar']
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double extends \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\AbstractDoz implements \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\DozInterface {

    public function foo() {}
    public function bar() {}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("overrides all parent class method and respect typehints using the layer option", function () {

            $result = Double::generate([
                'class'   => 'Kahlan\Spec\Plugin\Double\Double',
                'extends' => 'Kahlan\Spec\Fixture\Plugin\Double\Doz',
                'layer'   => true
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\Spec\\Plugin\\Double;

class Double extends \\Kahlan\\Spec\\Fixture\\Plugin\\Double\\Doz {

    public function foo(\$a) {return parent::foo(\$a);}
    public function foo2(\$b = NULL) {return parent::foo2(\$b);}
    public function foo3(array \$b = array()) {return parent::foo3(\$b);}
    public function foo4(callable \$fct) {return parent::foo4(\$fct);}
    public function foo5(\\Closure \$fct) {return parent::foo5(\$fct);}
    public function foo6(\\Exception \$e) {return parent::foo6(\$e);}
    public function foo7(\\Kahlan\\Spec\\Fixture\\Plugin\\Double\\DozInterface \$instance) {return parent::foo7(\$instance);}

}
?>
EOD;
            expect($result)->toBe($expected);

        });

        it("overrides by default all parent class method of internal classes if the layer option is not defined", function () {

            $double = Double::instance(['extends' => 'DateTime']);

            allow($double)->toReceive('getTimestamp')->andReturn(12345678);

            expect($double->getTimestamp())->toBe(12345678);

        });

        it("adds ` = NULL` to optional parameter in PHP core method", function () {

            skipIf(defined('HHVM_VERSION'));

            $result = Double::generate([
                'class'   => 'Kahlan\Spec\Plugin\Double\Double',
                'extends' => 'LogicException',
                'layer'   => true
            ]);

            $expected = <<<EOD
<?php
namespace Kahlan\\\\Spec\\\\Plugin\\\\Double;

class Double extends \\\\LogicException {

    public function __construct\\(\\\$message = NULL, \\\$code = NULL, \\\$previous = NULL\\)
EOD;
            expect($result)->toMatch('~' . $expected . '~i');

        });

        it("generates code without PHP tags", function () {

            $result = Double::generate([
                'class' => 'Kahlan\Spec\Plugin\Double\Double',
                'magicMethods' => false,
                'openTag' => false,
                'closeTag' => false,
            ]);

            $expected = <<<EOD
namespace Kahlan\\Spec\\Plugin\\Double;

class Double {



}

EOD;
            expect($result)->toBe($expected);

        });

    });

});
