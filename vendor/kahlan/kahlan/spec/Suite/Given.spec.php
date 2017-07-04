<?php
namespace Kahlan\Spec\Suite;

use Exception;
use Kahlan\Scope;
use Kahlan\Given;

describe("Given", function () {

    given('scope', function () {
        return 'root';
    });

    context("using the global `given()` function", function () {

        it("gets a lazy loadable variable", function () {

            given('firstname', function () {
                return 'Willy';
            });
            expect($this->firstname)->toBe('Willy');

        });

        it("lazy loads variables in cascades", function () {

            given('firstname', function () {
                return 'Johnny';
            });
            given('fullname', function () {
                return "{$this->firstname} {$this->lastname}";
            });
            given('lastname', function () {
                return 'Boy';
            });
            expect($this->fullname)->toBe('Johnny Boy');

        });

        it("allows to reference a lazy loadable variable which get overrided", function () {

            given('variable', function () {
                return [];
            });
            given('variable', function () {
                $this->variable[] = 1;
                return $this->variable;
            });
            given('variable', function () {
                $this->variable[] = 2;
                return $this->variable;
            });
            expect($this->variable)->toBe([1, 2]);

        });

        context("with a nested scope", function () {

            $count = 0;
            given('count', function () use (&$count) {
                return ++$count;
            });

            it("caches lazy loaded variables", function () {

                expect($this->count)->toBe(1);
                expect($this->count)->toBe(1);
                expect($this->count)->toBe(1);

            });

            it("doesn't cache across specifications",  function () {

                expect($this->count)->toBe(2);
                expect($this->count)->toBe(2);
                expect($this->count)->toBe(2);

            });
        });

        context("when loading a given variable during beforeEach", function () {

            $count = 0;
            given('count', function () use (&$count) {
                return ++$count;
            });

            beforeEach(function () {
                $this->count;
            });

            it("caches lazy loaded variables", function () {

                expect($this->count)->toBe(1);
                expect($this->count)->toBe(1);
                expect($this->count)->toBe(1);

            });

            it("doesn't cache across specifications",  function () {

                expect($this->count)->toBe(2);
                expect($this->count)->toBe(2);
                expect($this->count)->toBe(2);

            });
        });

        context("when loading a given variable during a nested beforeEach", function () {

            $count = 0;
            given('count', function () use (&$count) {
                return ++$count;
            });

            context('beforeEach nested to given', function () {
                beforeEach(function () {
                    $this->count;
                });

                it("caches lazy loaded variables", function () {

                    expect($this->count)->toBe(1);
                    expect($this->count)->toBe(1);
                    expect($this->count)->toBe(1);

                });

                it("doesn't cache across specifications",  function () {

                    expect($this->count)->toBe(2);
                    expect($this->count)->toBe(2);
                    expect($this->count)->toBe(2);

                });
            });
        });

        context('using a nested context', function () {

            it("gets a lazy loadable variable defined in a parent context", function () {

                expect($this->scope)->toBe('root');

            });

            it("can override a lazy loadable variable defined in a parent context", function () {

                given('scope', function () {
                    return 'nested';
                });
                expect($this->scope)->toBe('nested');

            });

        });

        context("using lazy loadable variables through `beforeEach()`", function () {

            beforeEach(function () {
                $this->value = $this->state;
            });

            given('state',  function () {
                return 'some_state';
            });

            it("makes lazy loadable variables loaded", function () {

                expect($this->value)->toBe('some_state');

            });

        });

        it("throw an exception when the second parameter is not a closure", function () {

            $closure = function () {
                given('some_name',  'some value');
            };
            expect($closure)->toThrow(new Exception("A closure is required by `Given` constructor."));

        });

        it("throw an exception for reserved keywords", function () {

            foreach (Scope::$blacklist as $keyword => $bool) {
                $closure = function () use ($keyword) {
                    given($keyword,  function () {
                        return 'some value';
                    });
                };
                expect($closure)->toThrow(new Exception("Sorry `{$keyword}` is a reserved keyword, it can't be used as a scope variable."));
            }

        });

    });

    describe("->__get()", function () {

        it("throw an new exception when trying to access an undefined variable through a given definition", function () {

            $closure = function () {
                $given = new Given(function () {
                    return $this->undefinedVariable;
                });
                $given();
            };
            expect($closure)->toThrow(new Exception("Undefined variable `undefinedVariable`."));

        });

    });

});
