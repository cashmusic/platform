<?php
namespace Kahlan\Spec\Suite;

use stdClass;
use Exception;
use InvalidArgumentException;

use Kahlan\MissingImplementationException;
use Kahlan\PhpErrorException;
use Kahlan\Suite;
use Kahlan\Matcher;
use Kahlan\Arg;
use Kahlan\Plugin\Double;

describe("Suite", function () {

    beforeAll(function () {
        Suite::$PHP = 5;
    });

    afterAll(function () {
        Suite::$PHP = PHP_MAJOR_VERSION;
    });

    beforeEach(function () {
        $this->suite = new Suite(['matcher' => new Matcher()]);
    });

    describe("->run()", function () {

        it("run the suite", function () {

            $describe = $this->suite->describe("", function () {

                $this->it("runs a spec", function () {
                    $this->expect(true)->toBe(true);
                });

            });

            $this->suite->run();
            expect($this->suite->status())->toBe(0);
            expect($this->suite->passed())->toBe(true);

        });

        it("calls `afterX` callbacks if an exception occurs during callbacks", function () {

            $describe = $this->suite->describe("", function () {

                $this->inAfterEach = 0;

                $this->beforeEach(function () {
                    throw new Exception('Breaking the flow should execute afterEach anyway.');
                });

                $this->it("does nothing", function () {
                });

                $this->afterEach(function () {
                    $this->inAfterEach++;
                });

            });

            $this->suite->run();

            expect($describe->inAfterEach)->toBe(1);

            $results = $this->suite->summary()->logs('errored');
            expect($results)->toHaveLength(1);

            $report = reset($results);
            $actual = $report->exception()->getMessage();
            expect($actual)->toBe('Breaking the flow should execute afterEach anyway.');

            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

        it("logs `MissingImplementationException` when thrown", function () {

            $missing = new MissingImplementationException();

            $describe = $this->suite->describe("", function () use ($missing) {

                $this->it("throws an `MissingImplementationException`", function () use ($missing) {
                    throw $missing;
                });

            });

            $this->suite->run();

            $results = $this->suite->summary()->logs('errored');
            expect($results)->toHaveLength(1);

            $report = reset($results);
            expect($report->exception())->toBe($missing);
            expect($report->type())->toBe('errored');
            expect($report->messages())->toBe(['', '', 'it throws an `MissingImplementationException`']);

            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

        it("throws and exception if attempts to call the `run()` function inside a scope", function () {

            $closure = function () {
                $describe = $this->suite->describe("", function () {
                    $this->run();
                });
                $this->suite->run();
            };

            expect($closure)->toThrow(new Exception('Method not allowed in this context.'));

            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

        it("fails fast", function () {

            $describe = $this->suite->describe("", function () {

                $this->it("fails1", function () {
                    $this->expect(true)->toBe(false);
                });

                $this->it("fails2", function () {
                    $this->expect(true)->toBe(false);
                });

                $this->it("fails3", function () {
                    $this->expect(true)->toBe(false);
                });

            });

            $this->suite->run(['ff' => 1]);

            $failed = $this->suite->summary()->logs('failed');

            expect($failed)->toHaveLength(1);
            expect($this->suite->focused())->toBe(false);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

        it("fails after two failures", function () {

            $describe = $this->suite->describe("", function () {

                $this->it("fails1", function () {
                    $this->expect(true)->toBe(false);
                });

                $this->it("fails2", function () {
                    $this->expect(true)->toBe(false);
                });

                $this->it("fails3", function () {
                    $this->expect(true)->toBe(false);
                });

            });

            $this->suite->run(['ff' => 2]);

            $failed = $this->suite->summary()->logs('failed');

            expect($failed)->toHaveLength(2);
            expect($this->suite->focused())->toBe(false);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

    });

    describe("skipIf", function () {

        it("skips specs in a before", function () {

            $describe = $this->suite->describe("skip suite", function () {

                $this->exectuted = ['it' => 0];

                beforeAll(function () {
                    skipIf(true);
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

            });
            $reporters = Double::instance();

            expect($reporters)->toReceive('dispatch')->with('start', ['total' => 2])->ordered;
            expect($reporters)->toReceive('dispatch')->with('suiteStart', $describe)->ordered;
            expect($reporters)->toReceive('dispatch')->with('specStart', Arg::toBeAnInstanceOf('Kahlan\Specification'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('specEnd', Arg::toBeAnInstanceOf('Kahlan\Log'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('specStart', Arg::toBeAnInstanceOf('Kahlan\Specification'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('specEnd', Arg::toBeAnInstanceOf('Kahlan\Log'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('suiteEnd', $describe)->ordered;
            expect($reporters)->toReceive('dispatch')->with('end', Arg::toBeAnInstanceOf('Kahlan\Summary'))->ordered;

            $this->suite->run(['reporters' => $reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0]);
            expect($this->suite->focused())->toBe(false);
            expect($this->suite->status())->toBe(0);
            expect($this->suite->passed())->toBe(true);

        });

        it("skips specs in a beforeEach", function () {

            $describe = $this->suite->describe("skip suite", function () {

                $this->exectuted = ['it' => 0];

                beforeEach(function () {
                    skipIf(true);
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

            });

            $reporters = Double::instance();

            expect($reporters)->toReceive('dispatch')->with('start', ['total' => 2])->ordered;
            expect($reporters)->toReceive('dispatch')->with('suiteStart', $describe)->ordered;
            expect($reporters)->toReceive('dispatch')->with('specStart', Arg::toBeAnInstanceOf('Kahlan\Specification'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('specEnd', Arg::toBeAnInstanceOf('Kahlan\Log'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('specStart', Arg::toBeAnInstanceOf('Kahlan\Specification'))->ordered;
            expect($reporters)->toReceive('dispatch')->with('suiteEnd', $describe)->ordered;
            expect($reporters)->toReceive('dispatch')->with('end', Arg::toBeAnInstanceOf('Kahlan\Summary'))->ordered;

            $this->suite->run(['reporters' => $reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0]);
            expect($this->suite->focused())->toBe(false);
            expect($this->suite->status())->toBe(0);
            expect($this->suite->passed())->toBe(true);

        });

    });

});
