<?php
namespace Kahlan\Spec\Suite\Plugin;

use Kahlan\Jit\Interceptor;
use Kahlan\QuitException;
use Kahlan\Plugin\Quit;
use Kahlan\Jit\Patcher\Quit as QuitPatcher;

use Kahlan\Spec\Fixture\Plugin\Quit\Foo;

describe("Quit", function () {

    /**
     * Save current & reinitialize the Interceptor class.
     */
    beforeAll(function () {
        $this->previous = Interceptor::instance();
        Interceptor::unpatch();

        $cachePath = rtrim(sys_get_temp_dir(), DS) . DS . 'kahlan';
        $include = ['Kahlan\Spec\\'];
        $interceptor = Interceptor::patch(compact('include', 'cachePath'));
        $interceptor->patchers()->add('quit', new QuitPatcher());
    });

    /**
     * Restore Interceptor class.
     */
    afterAll(function () {
        Interceptor::load($this->previous);
    });

    describe("::enable()", function () {

        it("enables quit statements", function () {

            Quit::disable();
            expect(Quit::enabled())->toBe(false);

            Quit::enable();
            expect(Quit::enabled())->toBe(true);

        });

    });

    describe("::disable()", function () {

        it("disables quit statements", function () {

            Quit::enable();
            expect(Quit::enabled())->toBe(true);

            Quit::disable();
            expect(Quit::enabled())->toBe(false);

        });

    });

    describe("::disable()", function () {

        it("throws an exception when an exit statement occurs if not allowed", function () {

            Quit::disable();

            $closure = function () {
                $foo = new Foo();
                $foo->exitStatement(-1);
            };

            expect($closure)->toThrow(new QuitException('Exit statement occurred', -1));

        });

    });

    describe("::disable()", function () {

        it("throws an exception when a die statement occurs with string message", function () {

            Quit::disable();

            $closure = function () {
                $foo = new Foo();
                $foo->dieStatement('error message');
            };

            expect($closure)->toThrow(new QuitException('Exit statement occurred with message: error message', 0));

        });

    });

    describe("::disable()", function () {

        it("throws an exception when a die statement occurs with integer code", function () {

            Quit::disable();

            $closure = function () {
                $foo = new Foo();
                $foo->dieStatement(-1);
            };

            expect($closure)->toThrow(new QuitException('Exit statement occurred', -1));

        });

    });

    describe("::reset()", function () {

        it("enables `exit()` call catching on reset", function () {

            Quit::disable();
            expect(Quit::enabled())->toBe(false);
            Quit::reset();
            expect(Quit::enabled())->toBe(true);

        });

    });

});
