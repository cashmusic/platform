<?php
namespace Kahlan\Spec\Suite\Matcher;

use Exception;

use Kahlan\Jit\Interceptor;
use Kahlan\Jit\Patcher\Pointcut as PointcutPatcher;
use Kahlan\Jit\Patcher\Monkey as MonkeyPatcher;
use Kahlan\Matcher\ToBeCalled;

use Kahlan\Spec\Fixture\Plugin\Monkey\Mon;

describe("ToBeCalled", function () {

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

    describe("::match()", function () {

        it("expects uncalled function to be uncalled", function () {

            $mon = new Mon();
            expect('time')->not->toBeCalled();

        });

        it("expects called function to be called", function () {

            $mon = new Mon();
            expect('time')->toBeCalled();
            $mon->time();

        });

        context("when using with()", function () {

            it("expects called function called with correct arguments to be called", function () {

                $mon = new Mon();
                expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->toBeCalled()->with(5, 10);
                $mon->rand(5, 10);

            });

            it("expects called function called with correct arguments exactly a specified times to be called", function () {

                $mon = new Mon();
                expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->toBeCalled()->with(5, 10)->times(2);
                $mon->rand(5, 10);
                $mon->rand(5, 10);

            });

            it("expects called function called with correct arguments not exactly a specified times to be uncalled", function () {

                $mon = new Mon();
                expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->not->toBeCalled()->with(5, 10)->times(2);
                $mon->rand(5, 10);
                $mon->rand(10, 10);

            });

        });

        context("when using times()", function () {

            it("expects called function to be called exactly once", function () {

                $mon = new Mon();
                expect('time')->toBeCalled()->once();
                $mon->time();

            });

            it("expects called function to be called exactly a specified times", function () {

                $mon = new Mon();
                expect('time')->toBeCalled()->times(3);
                $mon->time();
                $mon->time();
                $mon->time();

            });

            it("expects called function not called exactly a specified times to be uncalled", function () {

                $mon = new Mon();
                expect('time')->not->toBeCalled()->times(1);
                $mon->time();
                $mon->time();

            });

        });

        context("with ordered enabled", function () {

            describe("::match()", function () {

                it("expects uncalled function to be uncalled in a defined order", function () {

                    $mon = new Mon();
                    expect('time')->toBeCalled()->ordered;
                    expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->not->toBeCalled()->ordered;
                    $mon->time();

                });

                it("expects called function to be called in a defined order", function () {

                    $mon = new Mon();
                    expect('time')->toBeCalled()->ordered;
                    expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->toBeCalled()->with(5, 10)->ordered;
                    expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->toBeCalled()->with(10, 20)->ordered;
                    $mon->time();
                    $mon->rand(5, 10);
                    $mon->rand(10, 20);

                });

                it("expects called function called in a different order to be uncalled", function () {

                    $mon = new Mon();
                    expect('time')->toBeCalled()->ordered;
                    expect('Kahlan\Spec\Fixture\Plugin\Monkey\rand')->not->toBeCalled()->with(5, 10)->ordered;
                    $mon->rand(5, 10);
                    $mon->time();

                });

            });

        });

    });

    describe("->description()", function () {

        it("returns the description message for not received call", function () {

            $mon = new Mon();
            $matcher = new ToBeCalled('time');

            $matcher->resolve([
                'instance' => $matcher,
                'data'     => [
                    'actual' => 'time',
                    'logs'   => []
                ]
            ]);

            $actual = $matcher->description();

            expect($actual['description'])->toBe('be called.');
            expect($actual['data'])->toBe([
                'actual' => 'time()',
                'actual called times' => 0,
                'expected to be called' => 'time()'
            ]);

        });

        it("returns the description message for not received call the specified number of times", function () {

            $mon = new Mon();
            $matcher = new ToBeCalled('time');
            $matcher->times(2);

            $matcher->resolve([
                'instance' => $matcher,
                'data'     => [
                    'actual' => 'time',
                    'logs'   => []
                ]
            ]);

            $actual = $matcher->description();

            expect($actual['description'])->toBe('be called the expected times.');
            expect($actual['data'])->toBe([
                'actual' => 'time()',
                'actual called times' => 0,
                'expected to be called' => 'time()',
                'expected called times' => 2
            ]);

        });

        it("returns the correct number of actually called times", function () {

            $mon = new Mon();
            $matcher = new ToBeCalled('time');

            $mon->time();
            $mon->time();
            $mon->time();

            $matcher->resolve([
                'instance' => $matcher,
                'data'     => [
                    'actual' => 'time',
                    'logs'   => []
                ]
            ]);

            $actual = $matcher->description();

            expect($actual['description'])->toBe('be called.');
            expect($actual['data'])->toBe([
                'actual' => 'time()',
                'actual called times' => 3,
                'expected to be called' => 'time()'
            ]);

        });

        it("returns the correct number of actually called times when a limit is defined", function () {

            $mon = new Mon();
            $matcher = new ToBeCalled('time');
            $matcher->times(2);

            $mon->time();
            $mon->time();
            $mon->time();

            $matcher->resolve([
                'instance' => $matcher,
                'data'     => [
                    'actual' => 'time',
                    'logs'   => []
                ]
            ]);

            $actual = $matcher->description();

            expect($actual['description'])->toBe('be called the expected times.');
            expect($actual['data'])->toBe([
                'actual' => 'time()',
                'actual called times' => 3,
                'expected to be called' => 'time()',
                'expected called times' => 2
            ]);

        });

        it("returns the description message for wrong passed arguments", function () {

            $mon = new Mon();
            $matcher = new ToBeCalled('time');
            $matcher->with('Hello World!');

            $mon->time();

            $matcher->resolve([
                'instance' => $matcher,
                'data'     => [
                    'actual' => 'time',
                    'logs'   => []
                ]
            ]);

            $actual = $matcher->description();

            expect($actual['description'])->toBe('be called with expected parameters.');
            expect($actual['data'])->toBe([
                'actual' => 'time()',
                'actual called times' => 1,
                'actual called parameters list' => [
                   []
                ],
                'expected to be called' => 'time()',
                'expected parameters' => [
                    'Hello World!'
                ]
            ]);

        });

    });

    describe("->ordered()", function () {

        it("throw an exception when trying to play with core instance", function () {

            expect(function () {
                $matcher = new ToBeCalled('a');
                $matcher->order;
            })->toThrow(new Exception("Unsupported attribute `order` only `ordered` is available."));

        });

    });

});
