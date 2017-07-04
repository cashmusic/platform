<?php
namespace Kahlan\Spec\Suite;

use stdClass;
use Exception;
use InvalidArgumentException;

use Kahlan\MissingImplementationException;
use Kahlan\PhpErrorException;
use Kahlan\Suite;
use Kahlan\Matcher;
use Kahlan\Reporters;
use Kahlan\Arg;
use Kahlan\Plugin\Double;

describe("Suite", function () {

    beforeEach(function () {
        $this->suite = new Suite(['matcher' => new Matcher()]);
        $this->reporters = new Reporters();
    });

    describe("->__construct()", function () {

        it("throws an exception with invalid closure", function () {
            $closure = function () {
                $this->suite = new Suite([
                    'closure' => null,
                    'parent'  => new Suite()
                ]);
            };
            expect($closure)->toThrow(new Exception('Error, invalid closure.'));
        });

    });

    context("when inspecting flow", function () {

        describe("->beforeAll()", function () {

            $this->nb = 0;

            beforeAll(function () {
                $this->nb++;
            });

            it("passes if `before` has been executed", function () use (&$nb) {

                expect($this->nb)->toBe(1);

            });

            it("passes if `before` has not been executed twice", function () use (&$nb) {

                expect($this->nb)->toBe(1);

            });

        });

        describe("->beforeEach()", function () {

            $this->nb = 0;

            beforeEach(function () {
                $this->nb++;
            });

            it("passes if `beforeEach` has been executed", function () {

                expect($this->nb)->toBe(1);

            });

            it("passes if `beforeEach` has been executed twice", function () {

                expect($this->nb)->toBe(2);

            });

            context("with sub scope", function () {

                it("passes if `beforeEach` has been executed once more", function () {

                    expect($this->nb)->toBe(3);

                });

            });

            it("passes if `beforeEach` has been executed once more", function () {

                expect($this->nb)->toBe(4);

            });

        });

        describe("->afterAll()", function () {

            $this->nb = 0;

            afterAll(function () {
                $this->nb++;
            });

            it("passes if `after` has not been executed", function () {

                expect($this->nb)->toBe(0);

            });

        });

        describe("->afterEach()", function () {

            $this->nb = 0;

            afterEach(function () {
                $this->nb++;
            });

            it("passes if `afterEach` has not been executed", function () {

                expect($this->nb)->toBe(0);

            });

            it("passes if `afterEach` has been executed", function () {

                expect($this->nb)->toBe(1);

            });

            context("with sub scope", function () {

                it("passes if `afterEach` has been executed once more", function () {

                    expect($this->nb)->toBe(2);

                });

            });

            it("passes if `afterEach` has been executed once more", function () {

                expect($this->nb)->toBe(3);

            });

        });

    });

    describe("->describe()", function () {

        it("creates a sub suite of specs inside the root suite", function () {

            $suite = $this->suite->describe("->method()", function () {});

            expect($suite->message())->toBe('->method()');
            expect($suite->parent())->toBe($this->suite);

            $suites = $this->suite->children();
            expect($suite)->toBe(end($suites));

        });

    });

    describe("->context()", function () {

        it("creates a contextualized suite of specs inside the root suite", function () {

            $suite = $this->suite->context("->method()", function () {});

            expect($suite->message())->toBe('->method()');
            expect($suite->parent())->toBe($this->suite);

            $suites = $this->suite->children();
            expect($suite)->toBe(end($suites));

        });

    });

    describe("->it()", function () {

        it("creates a spec", function () {

            $this->suite->it("does some things", function () {});

            $specs = $this->suite->children();
            $it = end($specs);

            expect($it->message())->toBe('it does some things');
            expect($it->parent())->toBe($this->suite);

        });

        it("creates a spec with a random message if not set", function () {

            $this->suite->it(function () {});

            $specs = $this->suite->children();
            $it = end($specs);

            expect($it->message())->toMatch('~^it spec #[0-9]+$~');

        });

    });

    describe("->beforeAll()", function () {

        it("creates a before callback", function () {

            $callbacks = $this->suite->callbacks('beforeAll');
            expect($callbacks)->toHaveLength(0);

            $this->suite->beforeAll(function () {});
            $callbacks = $this->suite->callbacks('beforeAll');
            expect($callbacks)->toHaveLength(1);

        });

        it("captures errors", function () {

            $describe = $this->suite->describe("", function () {

                $this->beforeAll(function () {
                    $undefined++;
                });

                $this->it("it", function () {
                    $this->expect(true)->toBe(true);
                });

            });

            $this->suite->run(['reporters' => $this->reporters]);
            $summary = $this->suite->summary();

            expect($summary->passed())->toBe(0);
            expect($summary->errored())->toBe(1);

            $errors = $summary->logs('errored');

            expect($errors)->toHaveLength(1);
            $log = reset($errors);

            expect($log->exception()->getMessage())->toBe("`E_NOTICE` Undefined variable: undefined");
            expect($this->suite->total())->toBe(1);

            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->focused())->toBe(false);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

    });

    describe("->afterAll()", function () {

        it("creates a before callback", function () {

            $callbacks = $this->suite->callbacks('afterAll');
            expect($callbacks)->toHaveLength(0);

            $this->suite->afterAll(function () {});
            $callbacks = $this->suite->callbacks('afterAll');
            expect($callbacks)->toHaveLength(1);

        });

        it("captures errors", function () {

            $describe = $this->suite->describe("", function () {

                $this->afterAll(function () {
                    $undefined++;
                });

                $this->it("it", function () {
                    $this->expect(true)->toBe(true);
                });

            });

            $this->suite->run(['reporters' => $this->reporters]);
            $summary = $this->suite->summary();

            expect($summary->passed())->toBe(1);
            expect($summary->errored())->toBe(1);

            $errors = $summary->logs('errored');

            expect($errors)->toHaveLength(1);
            $log = reset($errors);

            expect($log->exception()->getMessage())->toBe("`E_NOTICE` Undefined variable: undefined");
            expect($this->suite->total())->toBe(1);

            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->focused())->toBe(false);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(false);

        });

    });

    describe("->beforeEach()", function () {

        it("creates a beforeEach callback", function () {

            $callbacks = $this->suite->callbacks('beforeEach');
            expect($callbacks)->toHaveLength(0);

            $this->suite->beforeEach(function () {});
            $callbacks = $this->suite->callbacks('beforeEach');
            expect($callbacks)->toHaveLength(1);

        });

    });

    describe("->afterEach()", function () {

        it("creates a before callback", function () {

            $callbacks = $this->suite->callbacks('afterEach');
            expect($callbacks)->toHaveLength(0);

            $this->suite->afterEach(function () {});
            $callbacks = $this->suite->callbacks('afterEach');
            expect($callbacks)->toHaveLength(1);

        });

    });

    describe("->total()/->enabled()", function () {

        it("return the total/enabled number of specs", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->describe("fdescribe", function () {

                    $this->it("it", function () {
                        $this->exectuted['it']++;
                    });

                    $this->describe("describe", function () {

                        $this->fit("fit", function () {
                            $this->exectuted['fit']++;
                        });

                        $this->it("it", function () {
                            $this->exectuted['it']++;
                        });

                    });

                });

            });

            expect($this->suite->total())->toBe(3);
            expect($this->suite->enabled())->toBe(1);

        });

    });

    describe("->fdescribe()", function () {

        it("executes only the `it` in focused mode", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->describe("->describe()", function () {

                    $this->it("it", function () {
                        $this->exectuted['it']++;
                    });

                });

                $this->fdescribe("->fdescribe()", function () {

                    $this->fit("fit", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->it("it", function () {
                        $this->exectuted['it']++;
                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0, 'fit' => 1]);
            expect($this->suite->total())->toBe(3);
            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

        it("executes all `it` in focused mode if no one is focused", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->fdescribe("->fdescribe()", function () {

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0, 'fit' => 2]);
            expect($this->suite->total())->toBe(2);
            expect($this->suite->enabled())->toBe(2);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

        it("executes all `it` in focused mode if no one is focused in a nested way", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->fdescribe("->fdescribe()", function () {

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->describe("->describe()", function () {

                        $this->it("assumes fit due to the parent", function () {
                            $this->exectuted['fit']++;
                        });

                        $this->it("assumes fit due to the parent", function () {
                            $this->exectuted['fit']++;
                        });

                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0, 'fit' => 4]);
            expect($this->suite->total())->toBe(4);
            expect($this->suite->enabled())->toBe(4);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

    });

    describe("->fcontext()", function () {

        it("executes only the `it` in focused mode", function () {

            $context = $this->suite->context("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->fcontext("->fcontext()", function () {

                    $this->fit("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['it']++;
                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($context->exectuted)->toEqual(['it' => 0, 'fit' => 1]);
            expect($this->suite->total())->toBe(2);
            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

        it("executes all `it` in focused mode if no one is focused", function () {

            $context = $this->suite->context("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->fcontext("->fcontext()", function () {

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($context->exectuted)->toEqual(['it' => 0, 'fit' => 2]);
            expect($this->suite->total())->toBe(2);
            expect($this->suite->enabled())->toBe(2);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

        it("executes all `it` in focused mode if no one is focused in a nested way", function () {

            $context = $this->suite->context("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->fcontext("->fcontext()", function () {

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->it("assumes fit due to the parent", function () {
                        $this->exectuted['fit']++;
                    });

                    $this->context("->context()", function () {

                        $this->it("assumes fit due to the parent", function () {
                            $this->exectuted['fit']++;
                        });

                        $this->it("assumes fit due to the parent", function () {
                            $this->exectuted['fit']++;
                        });

                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($context->exectuted)->toEqual(['it' => 0, 'fit' => 4]);
            expect($this->suite->total())->toBe(4);
            expect($this->suite->enabled())->toBe(4);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

    });

    describe("->fit()", function () {

        it("executes only the focused `it`", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->fit("an fit", function () {
                    $this->exectuted['fit']++;
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->fit("an fit", function () {
                    $this->exectuted['fit']++;
                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0, 'fit' => 2]);
            expect($this->suite->total())->toBe(4);
            expect($this->suite->enabled())->toBe(2);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

        it("propagates the exclusivity up to parents", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->fdescribe("fdescribe", function () {

                    $this->describe("describe", function () {

                        $this->it("it", function () {
                            $this->exectuted['it']++;
                        });

                        $this->fit("fit", function () {
                            $this->exectuted['fit']++;
                        });

                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0, 'fit' => 1]);
            expect($this->suite->total())->toBe(2);
            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

        it("propagates the exclusivity up to parents", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->describe("fdescribe", function () {

                    $this->it("it", function () {
                        $this->exectuted['it']++;
                    });

                    $this->describe("describe", function () {

                        $this->fit("fit", function () {
                            $this->exectuted['fit']++;
                        });

                        $this->it("it", function () {
                            $this->exectuted['it']++;
                        });

                    });

                });

            });

            $this->suite->run(['reporters' => $this->reporters]);

            expect($describe->exectuted)->toEqual(['it' => 0, 'fit' => 1]);
            expect($this->suite->total())->toBe(3);
            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->focused())->toBe(true);
            expect($this->suite->status())->toBe(-1);
            expect($this->suite->passed())->toBe(true);

        });

    });

    describe("->focused()", function () {

        it("returns the references of runned focused specs", function () {

            $describe = $this->suite->describe("focused suite", function () {

                $this->exectuted = ['it' => 0, 'fit' => 0];

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->fit("an fit", function () {
                    $this->exectuted['fit']++;
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->fit("an fit", function () {
                    $this->exectuted['fit']++;
                });

            });

            $this->suite->run();

            expect($this->suite->summary()->get('focused'))->toHaveLength(2);

        });

    });

    describe("->xdecribe()", function () {

        it("propagates the exclusion down to children", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0];

                $this->it("it1", function () {
                    $this->exectuted['it']++;
                });

                $this->xdescribe("xdescribe", function () {

                    $this->it("it2", function () {
                        $this->exectuted['it']++;
                    });

                    $this->it("it3", function () {
                        $this->exectuted['it']++;
                    });

                });

            });

            $this->suite->run();

            expect($describe->exectuted)->toEqual(['it' => 1]);
            expect($this->suite->total())->toBe(3);
            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->status())->toBe(0);
            expect($this->suite->passed())->toBe(true);

        });

    });

    describe("->xcontext()", function () {

        it("propagates the exclusion down to children", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0];

                $this->it("it1", function () {
                    $this->exectuted['it']++;
                });

                $this->xcontext("xcontext", function () {

                    $this->it("it2", function () {
                        $this->exectuted['it']++;
                    });

                    $this->it("it3", function () {
                        $this->exectuted['it']++;
                    });

                });

            });

            $this->suite->run();

            expect($describe->exectuted)->toEqual(['it' => 1]);
            expect($this->suite->total())->toBe(3);
            expect($this->suite->enabled())->toBe(1);
            expect($this->suite->status())->toBe(0);
            expect($this->suite->passed())->toBe(true);

        });

    });

    describe("->xit()", function () {

        it("skips excluded `it`", function () {

            $describe = $this->suite->describe("", function () {

                $this->exectuted = ['it' => 0];

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

                $this->xit("an xit", function () {
                    $this->exectuted['it']++;
                });

                $this->it("an it", function () {
                    $this->exectuted['it']++;
                });

            });

            $this->suite->run();

            expect($describe->exectuted)->toEqual(['it' => 2]);
            expect($this->suite->total())->toBe(3);
            expect($this->suite->enabled())->toBe(2);
            expect($describe->children()[1]->excluded())->toBe(true);
            expect($this->suite->status())->toBe(0);
            expect($this->suite->passed())->toBe(true);

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

    describe("::hash()", function () {

        it("creates an hash from objects", function () {

            $instance = new stdClass();

            $hash1 = Suite::hash($instance);
            $hash2 = Suite::hash($instance);
            $hash3 = Suite::hash(new stdClass());

            expect($hash1)->toBe($hash2);
            expect($hash1)->not->toBe($hash3);

        });

        it("creates an hash from class names", function () {

            $class = 'hello\world\class';
            $hash = Suite::hash($class);
            expect($hash)->toBe($class);

        });

        it("Throws an exception if values are not string or objects", function () {

            $closure = function () {
                $hash = Suite::hash([]);
            };

            expect($closure)->toThrow(new InvalidArgumentException("Error, the passed argument is not hashable."));

        });

    });

    describe("::register()", function () {

        it("registers an hash", function () {

            $instance = new stdClass();

            $hash = Suite::hash($instance);
            Suite::register($hash);

            expect(Suite::registered($hash))->toBe(true);

        });

    });

    describe("::register()", function () {

        it("return `false` if the hash is not registered", function () {

            $instance = new stdClass();

            $hash = Suite::hash($instance);

            expect(Suite::registered($hash))->toBe(false);

        });

    });

    describe("::reset()", function () {

        it("clears registered hashes", function () {

            $instance = new stdClass();

            $hash = Suite::hash($instance);
            Suite::register($hash);

            expect(Suite::registered($hash))->toBe(true);

            Suite::reset();

            expect(Suite::registered($hash))->toBe(false);

        });

    });

    describe("->status()", function () {

        it("returns `0` if a specs suite passes", function () {

            $describe = $this->suite->describe("", function () {
                $this->it("passes", function () {
                    $this->expect(true)->toBe(true);
                });
            });

            $this->suite->run();
            expect($this->suite->status())->toBe(0);

        });

        it("returns `-1` if a specs suite fails", function () {

            $describe = $this->suite->describe("", function () {
                $this->it("fails", function () {
                    $this->expect(true)->toBe(false);
                });
            });

            $this->suite->run();
            expect($this->suite->status())->toBe(-1);

        });

        it("forces a specified return status", function () {

            $describe = $this->suite->describe("", function () {
                $this->it("passes", function () {
                    $this->expect(true)->toBe(true);
                });
            });

            $this->suite->run();
            expect($this->suite->status())->toBe(0);

            $this->suite->status(-1);
            expect($this->suite->status())->toBe(-1);

        });

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

        it("calls `afterEach` callbacks if an exception occurs during callbacks", function () {

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

        it("logs error if an exception is occuring during an `afterEach` callbacks", function () {

            $describe = $this->suite->describe("", function () {

                $this->it("does nothing", function () {
                });

                $this->afterEach(function () {
                    throw new Exception('Errors occured in afterEach should be logged anyway.');
                });

            });

            $this->suite->run();

            $results = $this->suite->summary()->logs('errored');

            expect($results)->toHaveLength(1);

            $report = reset($results);
            $actual = $report->exception()->getMessage();
            expect($actual)->toBe('Errors occured in afterEach should be logged anyway.');

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

            skipIf(defined('HHVM_VERSION') || PHP_MAJOR_VERSION < 7);

            $describe = $this->suite->describe("", function () {
                $this->run();
            });

            $this->suite->run();

            $results = $this->suite->summary()->logs('errored');
            expect($results)->toHaveLength(1);

            $report = reset($results);
            expect($report->exception()->getMessage())->toBe('Method not allowed in this context.');
            expect($report->type())->toBe('errored');
            expect($report->messages())->toBe(['', '']);

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

    describe("->_errorHandler()", function () {

        it("converts E_NOTICE error to an exception", function () {

            $closure = function () {
                $a = $b;
            };
            expect($closure)->toThrow(new PhpErrorException("`E_NOTICE` Undefined variable: b"));

        });

        it("converts E_WARNING error to an exception", function () {

            $closure = function () {
                $a = array_merge();
            };
            expect($closure)->toThrow(new PhpErrorException("`E_WARNING` array_merge() expects at least 1 parameter, 0 given"));

        });

        it("uses default error reporting settings", function () {

            $describe = $this->suite->describe("", function () {

                $this->describe("->_errorHandler()", function () {

                    $this->it("ignores E_NOTICE", function () {
                        $closure = function () {
                            $a = $b;
                        };
                        $this->expect($closure)->not->toThrow();
                    });

                });

            });

            error_reporting(E_ALL ^ E_NOTICE);
            $this->suite->run();
            error_reporting(E_ALL);

            expect($this->suite->passed())->toBe(true);

        });

    });

    describe("->reporters()", function () {

        it("returns the reporters", function () {

            $describe = $this->suite->describe("", function () {});

            $reporters = Double::instance();
            $this->suite->run(['reporters' => $reporters]);

            expect($this->suite->reporters())->toBe($reporters);

        });

    });

    describe("->stop()", function () {

        it("sends the stop event", function () {

            $describe = $this->suite->describe("", function () {});

            $reporters = Double::instance();

            expect($reporters)->toReceive('dispatch')->with('stop', Arg::toBeAnInstanceOf('Kahlan\Summary'));

            $this->suite->run(['reporters' => $reporters]);
            $this->suite->stop();

            expect($this->suite->reporters())->toBe($reporters);

        });

    });

});
