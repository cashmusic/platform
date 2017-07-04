<?php
namespace Kahlan\Spec\Suite;

use stdClass;
use Exception;
use Kahlan\Suite;
use Kahlan\Specification;
use Kahlan\Matcher;
use Kahlan\Plugin\Double;

describe("Specification", function () {

    beforeEach(function () {

        $this->spec = new Specification(['closure' => function () {}]);

    });

    describe("->__construct()", function () {

        it("sets spec as pending with empty closure", function () {

            $this->spec = new Specification(['closure' => null]);

            expect($this->spec->passed())->toBe(true);

            $pending = $this->spec->summary()->pending();
            expect($pending)->toBe(1);

        });

    });

    describe("->expect()", function () {

        it("returns the matcher instance", function () {

            $matcher = $this->spec->expect('actual');
            expect($matcher)->toBeAnInstanceOf('Kahlan\Expectation');

        });

    });

    describe("->waitsFor()", function () {

        it("allows non closure", function () {

            $this->spec = new Specification([
                'message' => 'allows non closure',
                'closure' => function () {
                    $this->waitsFor('something')->toBe('something');
                }
            ]);

            expect($this->spec->passed())->toBe(true);

        });

        it("returns the matcher instance setted with the correct timeout", function () {

            $matcher = $this->spec->waitsFor(function (){}, 10);
            expect($matcher)->toBeAnInstanceOf('Kahlan\Expectation');
            expect($matcher->timeout())->toBe(10);

            $matcher = $this->spec->waitsFor(function (){});
            expect($matcher)->toBeAnInstanceOf('Kahlan\Expectation');
            expect($matcher->timeout())->toBe(0);

        });

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

                $root = new Suite();
                $root->backtraceFocus(['*Spec.php', '*.spec.php']);
                $this->spec = new Specification([
                    'parent'  => $root,
                    'closure' => function () {
                        $this->expect(Double::instance())->not->toReceive('helloWorld');
                    }
                ]);

                expect($this->spec->passed())->toBe(true);

                $passes = $this->spec->summary()->logs('passed');
                expect($passes)->toHaveLength(1);

                $pass = reset($passes);
                $expectation = $pass->children()[0];

                $file = $expectation->file();
                expect($file)->toMatch('~Specification.spec.php$~');

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

            });

        });

        describe('when a spec errored', function () {

            it('logs the error', function () {
                $this->spec = new Specification([
                    'closure' => function () {
                        $foo = Double::instance(['magicMethods' => false]);
                        expect($foo)->toReceive('somethingdefined');
                        expect($foo->thisisnotdefined)->toBe('test');
                    }
                ]);

                expect($this->spec->passed())->toBe(false);
                expect($this->spec->log()->type())->toBe('errored');

                $errored = $this->spec->summary()->logs('errored');
                expect($errored)->toHaveLength(1);
            });

        });

    });

});
