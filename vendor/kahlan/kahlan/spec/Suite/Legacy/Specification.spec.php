<?php
namespace Kahlan\Spec\Suite;

use stdClass;
use Exception;
use Kahlan\Suite;
use Kahlan\Specification;
use Kahlan\Matcher;
use Kahlan\Plugin\Double;

describe("Specification", function () {

    beforeAll(function () {
        Suite::$PHP = 5;
    });

    afterAll(function () {
        Suite::$PHP = PHP_MAJOR_VERSION;
    });

    beforeEach(function () {

        $this->spec = new Specification(['closure' => function () {}]);

    });

    describe("->passed()", function () {

        it("returns the closure return value", function () {

            $this->spec = new Specification([
                'closure' => function () {
                    return 'hello world';
                }
            ]);

            $return = null;
            $this->spec->passed($return);
            expect($return)->toBe('hello world');

        });

        it("marks the spec as pending when an expectation is not verified", function () {

            $this->spec = new Specification([
                'closure' => function () {
                    $this->expect(true)->toBe(true);
                    $this->expect(true);
                }
            ]);

            expect($this->spec->passed())->toBe(true);
            expect($this->spec->log()->type())->toBe('pending');

        });

        context("when the specs passed", function () {

            it("logs a pass", function () {

                $this->spec = new Specification([
                    'message' => 'runs a spec',
                    'closure' => function () {
                        $this->expect(true)->toBe(true);
                    }
                ]);

                expect($this->spec->passed())->toBe(true);

                $passed = $this->spec->summary()->logs('passed');
                expect($passed)->toHaveLength(1);

                $pass = reset($passed);
                $expectation = $pass->children()[0];

                expect($expectation->matcher())->toBe('Kahlan\Matcher\ToBe');
                expect($expectation->matcherName())->toBe('toBe');
                expect($expectation->not())->toBe(false);
                expect($expectation->type())->toBe('passed');
                expect($expectation->data())->toBe([
                'actual'   => true,
                'expected' => true
                ]);
                expect($expectation->messages())->toBe(['it runs a spec']);

            });

            it("logs a pass with a deferred matcher", function () {

                $this->spec = new Specification([
                    'message' => 'runs a spec',
                    'closure' => function () {
                        $stub = Double::instance();
                        $this->expect($stub)->toReceive('methodName');
                        $stub->methodName();
                    }
                ]);

                expect($this->spec->passed())->toBe(true);

                $passes = $this->spec->summary()->logs('passed');
                expect($passes)->toHaveLength(1);

                $pass = reset($passes);
                $expectation = $pass->children()[0];

                expect($expectation->matcher())->toBe('Kahlan\Matcher\ToReceive');
                expect($expectation->matcherName())->toBe('toReceive');
                expect($expectation->not())->toBe(false);
                expect($expectation->type())->toBe('passed');
                expect($expectation->data())->toBe([
                'actual received'       => 'methodName',
                "actual received times" => 1,
                'expected to receive'   => 'methodName'
                ]);
                expect($expectation->description())->toBe('receive the expected method.');
                expect($expectation->messages())->toBe(['it runs a spec']);

            });

            it("logs the not attribute", function () {

                $this->spec = new Specification([
                    'closure' => function () {
                        $this->expect(true)->not->toBe(false);
                    }
                ]);

                expect($this->spec->passed())->toBe(true);

                $passes = $this->spec->summary()->logs('passed');
                expect($passes)->toHaveLength(1);

                $pass = reset($passes);
                $expectation = $pass->children()[0];

                expect($expectation->not())->toBe(true);

            });

            it("logs deferred matcher backtrace", function () {

                $this->spec = new Specification([
                    'closure' => function () {
                        $this->expect(Double::instance())->not->toReceive('helloWorld');
                    }
                ]);

                expect($this->spec->passed())->toBe(true);

                $passes = $this->spec->summary()->logs('passed');
                expect($passes)->toHaveLength(1);

                $pass = reset($passes);
                $expectation = $pass->children()[0];

                $backtrace = $expectation->backtrace();
                expect($backtrace[0]['file'])->toMatch('~ToReceive.php$~');

            });

            it("logs the not attribute with a deferred matcher", function () {

                $this->spec = new Specification([
                    'closure' => function () {
                        $stub = Double::instance();
                        $this->expect($stub)->not->toReceive('methodName');
                    }
                ]);

                expect($this->spec->passed())->toBe(true);

                $passes = $this->spec->summary()->logs('passed');
                expect($passes)->toHaveLength(1);

                $pass = reset($passes);
                $expectation = $pass->children()[0];

                expect($expectation->not())->toBe(true);

            });

            it("resets `not` to `false ` after any matcher call", function () {

                expect([])
                ->not->toBeNull()
                ->toBeA('array')
                ->toBeEmpty();

            });

        });

        context("when the specs failed", function () {

            it("logs a fail", function () {

                $this->spec = new Specification([
                    'message' => 'runs a spec',
                    'closure' => function () {
                        $this->expect(true)->toBe(false);
                    }
                ]);

                expect($this->spec->passed())->toBe(false);

                $failed = $this->spec->summary()->logs('failed');
                expect($failed)->toHaveLength(1);
                $failure = reset($failed);

                $expectation = $failure->children()[0];
                expect($expectation->matcher())->toBe('Kahlan\Matcher\ToBe');
                expect($expectation->matcherName())->toBe('toBe');
                expect($expectation->not())->toBe(false);
                expect($expectation->type())->toBe('failed');
                expect($expectation->data())->toBe([
                'actual'   => true,
                'expected' => false
                ]);
                expect($expectation->messages())->toBe(['it runs a spec']);
                expect($expectation->backtrace())->toBeAn('array');
            });

            it("logs a fail with a deferred matcher", function () {

                $this->spec = new Specification([
                    'message' => 'runs a spec',
                    'closure' => function () {
                        $stub = Double::instance();
                        $this->expect($stub)->toReceive('methodName');
                    }
                ]);

                expect($this->spec->passed())->toBe(false);

                $failed = $this->spec->summary()->logs('failed');
                expect($failed)->toHaveLength(1);

                $failure = reset($failed);

                $expectation = $failure->children()[0];
                expect($expectation->matcher())->toBe('Kahlan\Matcher\ToReceive');
                expect($expectation->matcherName())->toBe('toReceive');
                expect($expectation->not())->toBe(false);
                expect($expectation->type())->toBe('failed');
                expect($expectation->data())->toBe([
                'actual received calls' => [],
                'expected to receive'   => 'methodName'
                ]);
                expect($expectation->description())->toBe('receive the expected method.');
                expect($expectation->messages())->toBe(['it runs a spec']);
                expect($expectation->backtrace())->toBeAn('array');

            });

            it("logs the not attribute", function () {

                $this->spec = new Specification([
                    'closure' => function () {
                        $this->expect(true)->not->toBe(true);
                    }
                ]);

                expect($this->spec->passed())->toBe(false);

                $failures = $this->spec->summary()->logs('failed');
                expect($failures)->toHaveLength(1);

                $failure = reset($failures);
                $expectation = $failure->children()[0];

                expect($expectation->not())->toBe(true);

            });

            it("logs the not attribute with a deferred matcher", function () {

                $this->spec = new Specification([
                    'closure' => function () {
                        $stub = Double::instance();
                        $this->expect($stub)->not->toReceive('methodName');
                        $stub->methodName();
                    }
                ]);

                expect($this->spec->passed())->toBe(false);

                $failures = $this->spec->summary()->logs('failed');
                expect($failures)->toHaveLength(1);

                $failure = reset($failures);
                $expectation = $failure->children()[0];

                expect($expectation->not())->toBe(true);
                expect($expectation->not())->toBe(true);

            });

            it("logs sub spec fails", function () {

                $this->spec = new Specification([
                    'message' => 'runs a spec',
                    'closure' => function () {
                        $this->waitsFor(function () {
                            $this->expect(true)->toBe(false);
                        });
                    }
                ]);

                expect($this->spec->passed())->toBe(false);

                $failured = $this->spec->summary()->logs('failed');
                expect($failured)->toHaveLength(1);

                $failure = reset($failured);
                $expectation = $failure->children()[0];

                expect($expectation->matcher())->toBe('Kahlan\Matcher\ToBe');
                expect($expectation->matcherName())->toBe('toBe');
                expect($expectation->not())->toBe(false);
                expect($expectation->type())->toBe('failed');
                expect($expectation->data())->toBe([
                'actual'   => true,
                'expected' => false
                ]);
                expect($expectation->messages())->toBe(['it runs a spec']);
                expect($expectation->backtrace())->toBeAn('array');
            });

            it("logs the first failing spec only", function () {

                $this->spec = new Specification([
                    'message' => 'runs a spec',
                    'closure' => function () {
                        $this->waitsFor(function () {
                            $this->expect(true)->toBe(false);
                            return true;
                        })->toBe(false);
                    }
                ]);

                expect($this->spec->passed())->toBe(false);

                $failured = $this->spec->summary()->logs('failed');
                expect($failured)->toHaveLength(1);

                $failure = reset($failured);
                $expectation = $failure->children()[0];

                expect($expectation->matcher())->toBe('Kahlan\Matcher\ToBe');
                expect($expectation->matcherName())->toBe('toBe');
                expect($expectation->not())->toBe(false);
                expect($expectation->type())->toBe('failed');
                expect($expectation->data())->toBe([
                    'actual'   => true,
                    'expected' => false
                ]);
                expect($expectation->messages())->toBe(['it runs a spec']);
                expect($expectation->backtrace())->toBeAn('array');
            });

        });

    });

});
