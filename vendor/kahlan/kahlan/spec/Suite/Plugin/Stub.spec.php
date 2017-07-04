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
use Kahlan\Plugin\Stub;

use Kahlan\Spec\Fixture\Plugin\Monkey\User;
use Kahlan\Spec\Fixture\Plugin\Pointcut\Foo;
use Kahlan\Spec\Fixture\Plugin\Pointcut\SubBar;

describe("Stub", function () {

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

    describe("__construct()", function () {

        it("throws an exception when trying to stub an unexisting class", function () {

            $closure = function () {
                new Stub('My\Unexisting\Classname\Foo');
            };
            $message = "Can't Stub the unexisting class `My\\Unexisting\\Classname\\Foo`.";
            expect($closure)->toThrow(new InvalidArgumentException($message));

        });

    });

    describe("->methods()", function () {

        context("with an instance", function () {

            it("stubs methods using an array", function () {

                $foo = new Foo();
                Stub::on($foo)->methods([
                    'message' => function () {
                        return 'Good Evening World!';
                    },
                    'bar' => function () {
                        return 'Hello Bar!';
                    }
                ]);
                expect($foo->message())->toBe('Good Evening World!');
                expect($foo->bar())->toBe('Hello Bar!');

            });

            it("throw an exception with invalid definition", function () {

                $closure = function () {
                    $foo = new Foo();
                    Stub::on($foo)->methods([
                        'bar' => 'Hello Bar!'
                    ]);
                };
                $message = "Stubbed method definition for `bar` must be a closure or an array of returned value(s).";
                expect($closure)->toThrow(new InvalidArgumentException($message));

            });

        });

        context("with an class", function () {

            it("stubs methods using return values as an array", function () {

                Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo')->methods([
                    'message' => ['Good Evening World!', 'Good Bye World!'],
                    'bar' => ['Hello Bar!']
                ]);

                $foo = new Foo();
                expect($foo->message())->toBe('Good Evening World!');

                $foo2 = new Foo();
                expect($foo2->message())->toBe('Good Bye World!');

                $foo3 = new Foo();
                expect($foo3->bar())->toBe('Hello Bar!');

            });

            it("stubs methods using closure", function () {

                Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo')->methods([
                    'message' => function () {
                        return 'Good Evening World!';
                    },
                    'bar' => function () {
                        return 'Hello Bar!';
                    }
                ]);

                $foo = new Foo();
                expect($foo->message())->toBe('Good Evening World!');

                $foo2 = new Foo();
                expect($foo2->bar())->toBe('Hello Bar!');

            });

            it("throw an exception with invalid definition", function () {

                $closure = function () {
                    $foo = new Foo();
                    Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo')->methods([
                        'bar' => 'Hello Bar!'
                    ]);
                };
                $message = "Stubbed method definition for `bar` must be a closure or an array of returned value(s).";
                expect($closure)->toThrow(new InvalidArgumentException($message));

            });

        });

    });

    describe("::registered()", function () {

        describe("without provided hash", function () {

            it("returns an empty array when no instance are registered", function () {

                expect(Stub::registered())->toBe([]);

            });

            it("returns an array of registered instances", function () {

                Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo')->method('foo', function () {});

                expect(Stub::registered())->toBeA('array')->toBe([
                    'Kahlan\Spec\Fixture\Plugin\Pointcut\Foo'
                ]);

            });

        });

        describe("with provided hash", function () {

            it("returns `false` for registered stub", function () {

                expect(Stub::registered('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo'))->toBe(false);

            });

            it("returns `true` for registered stub", function () {

                Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo')->method('foo', function () {});

                expect(Stub::registered('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo'))->toBe(true);

            });

        });

    });

    describe("::on()", function () {

        it("throw when stub a method using closure and using andReturn()", function () {

            expect(function () {
                $foo = new Foo();
                Stub::on($foo)->method('message', function ($param) {
                    return $param;
                })->andReturn(true);
            })->toThrow(new Exception("Some closure(s) has already been set."));

        });

    });

    describe("::reset()", function () {

        beforeEach(function () {

            Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo')->method('foo', function () {});
            Stub::on('Kahlan\Spec\Fixture\Plugin\Pointcut\Bar')->method('bar', function () {});

        });

        it("clears all stubs", function () {

            Stub::reset();
            expect(Stub::registered())->toBe([]);

        });

        it("clears one stub", function () {

            Stub::reset('Kahlan\Spec\Fixture\Plugin\Pointcut\Foo');
            expect(Stub::registered())->toBe([
                'Kahlan\Spec\Fixture\Plugin\Pointcut\Bar'
            ]);

        });

    });

});
