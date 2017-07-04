<?php
namespace Kahlan\Kahlan\Spec\Suite\Plugin\Stub;

use Exception;
use Kahlan\Jit\Interceptor;
use Kahlan\Jit\Patcher\Pointcut as PointcutPatcher;
use Kahlan\Jit\Patcher\Monkey as MonkeyPatcher;
use Kahlan\Plugin\Stub;

use Kahlan\Spec\Fixture\Plugin\Pointcut\Foo;

describe("Method", function () {

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

    describe("->andReturn()", function () {

        it("sets a return value", function () {

            $foo = new Foo();
            $stub = allow($foo)->toReceive('message');
            $stub->andReturn('Aloha!');

            expect($foo->message())->toBe('Aloha!');
            expect($stub->actualReturn())->toBe('Aloha!');

        });

        it("sets return values", function () {

            $foo = new Foo();
            $stub = allow($foo)->toReceive('message');
            $stub->andReturn('Aloha!', 'Hello!');

            expect($foo->message())->toBe('Aloha!');
            expect($stub->actualReturn())->toBe('Aloha!');

            expect($foo->message())->toBe('Hello!');
            expect($foo->message())->toBe('Hello!');
            expect($stub->actualReturn())->toBe('Hello!');

        });

        it("throws when return is already set", function () {

            expect(function () {
                $foo = new Foo();
                $stub = allow($foo)->toReceive('message');
                $stub->andRun(function ($param) {
                    return $param;
                });

                $stub->andReturn('Ahoy!');

            })->toThrow(new Exception('Some closure(s) has already been set.'));

        });

    });

    describe("->andRun()", function () {

        it("sets a closure", function () {

            $foo = new Foo();
            $stub = allow($foo)->toReceive('message');
            $stub->andRun(function ($param) {
                return $param;
            });

            expect($foo->message('Aloha!'))->toBe('Aloha!');
            expect($stub->actualReturn())->toBe('Aloha!');

        });

        it("sets closures", function () {

            $foo = new Foo();
            $stub = allow($foo)->toReceive('message');
            $stub->andRun(function () {
                return 'Aloha!';
            }, function () {
                return 'Hello!';
            });

            expect($foo->message())->toBe('Aloha!');
            expect($stub->actualReturn())->toBe('Aloha!');

            expect($foo->message())->toBe('Hello!');
            expect($foo->message())->toBe('Hello!');
            expect($stub->actualReturn())->toBe('Hello!');

        });

        it("throws when return is already set", function () {

            expect(function () {
                $foo = new Foo();
                $stub = allow($foo)->toReceive('message');
                $stub->andReturn('Ahoy!');

                $stub->andRun(function ($param) {
                    return $param;
                });
            })->toThrow(new Exception('Some return value(s) has already been set.'));

        });

        it("throws when trying to pass non callable", function () {

            expect(function () {
                $foo = new Foo();
                $stub = allow($foo)->toReceive('message');

                $stub->andRun('String');
            })->toThrow(new Exception('The passed parameter is not callable.'));

        });

    });

});
