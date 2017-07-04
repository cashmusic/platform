<?php
namespace Kahlan\Spec\Suite\Filter;

use Exception;
use Kahlan\Plugin\Double;
use Kahlan\Filter\Filter;

describe("Filter", function () {

    beforeEach(function () {

        Filter::register('spec.my_prefix', function ($chain, $message) {
            $message = "My {$message}";
            return $chain->next($message);
        });

        Filter::register('spec.be_prefix', function ($chain, $message) {
            $message = "Be {$message}";
            return $chain->next($message);
        });

        Filter::register('spec.no_chain', function ($chain, $message) {
            return "No Man's {$message}";
        });

    });

    afterEach(function () {
        Filter::reset();
        Filter::enable();
    });

    context("with an instance context", function () {

        beforeEach(function () {
            $this->mock = Double::instance(['uses' => ['Kahlan\Filter\Behavior\Filterable']]);
            allow($this->mock)->toReceive('filterable')->andRun(function () {
                return Filter::on($this, 'filterable', func_get_args(), function ($chain, $message) {
                    return "Hello {$message}";
                });
            });
        });

        describe("::apply()", function () {

            it("applies a filter which override a parameter", function () {

                Filter::apply($this->mock, 'filterable', 'spec.my_prefix');
                expect($this->mock->filterable('World!'))->toBe('Hello My World!');

            });

            it("applies a filter which break the chain", function () {

                Filter::apply($this->mock, 'filterable', 'spec.no_chain');
                expect($this->mock->filterable('World!'))->toBe("No Man's World!");

            });

            it("applies a custom filter", function () {

                allow($this->mock)->toReceive('filterable')->andRun(function () {
                    $closure = function ($chain, $message) {
                        return "Hello {$message}";
                    };
                    $custom = function ($chain, $message) {
                        $message = "Custom {$message}";
                        return $chain->next($message);
                    };
                    return Filter::on($this, 'filterable', func_get_args(), $closure, [$custom]);
                });
                expect($this->mock->filterable('World!'))->toBe("Hello Custom World!");

            });

            it("applies all filter set on the classname", function () {

                Filter::apply(get_class($this->mock), 'filterable', 'spec.my_prefix');
                expect($this->mock->filterable('World!'))->toBe('Hello My World!');

            });

            it("throws an Exception when trying to apply a filter using an unexisting closure", function () {

                $closure = function () {
                    Filter::apply($this->mock, 'filterable', 'spec.unexisting_closure');
                };
                expect($closure)->toThrow(new Exception('Undefined filter `spec.unexisting_closure`.'));

            });

        });

        describe("::detach()", function () {

            it("detaches a filters", function () {

                Filter::apply($this->mock, 'filterable', 'spec.my_prefix');
                Filter::detach($this->mock, 'filterable', 'spec.my_prefix');
                expect($this->mock->filterable('World!'))->toBe('Hello World!');

            });

        });

        describe("::filters()", function () {

            it("gets filters of a context", function () {

                Filter::apply($this->mock, 'filterable', 'spec.my_prefix');
                $filters = Filter::filters($this->mock, 'filterable');
                expect($filters)->toBeAn('array')->toHaveLength(1);
                expect(reset($filters))->toBeAnInstanceOf('Closure');

            });

        });

        describe("::enable()", function () {

            it("disables the filter system", function () {
                Filter::apply($this->mock, 'filterable', 'spec.my_prefix');
                Filter::enable(false);
                expect($this->mock->filterable('World!'))->toBe('Hello World!');
            });

        });

    });

    context("with a class context", function () {

        beforeEach(function () {
            $this->class = Double::classname();
            allow($this->class)->toReceive('::filterable')->andRun(function () {
                return Filter::on(get_called_class(), 'filterable', func_get_args(), function ($chain, $message) {
                    return "Hello {$message}";
                });
            });
        });

        describe("::apply()", function () {

            it("applies a filter and override a parameter", function () {
                $class = $this->class;
                Filter::apply($class, 'filterable', 'spec.my_prefix');
                expect($class::filterable('World!'))->toBe('Hello My World!');
            });

            it("applies a filter and break the chain", function () {
                $class = $this->class;
                Filter::apply($class, 'filterable', 'spec.no_chain');
                expect($class::filterable('World!'))->toBe("No Man's World!");
            });

            it("applies parent classes's filters", function () {
                $class = $this->class;
                $subclass = Double::classname(['extends' => $class]);
                allow($subclass)->toReceive('::filterable')->andRun(function () {
                    return Filter::on(get_called_class(), 'filterable', func_get_args(), function ($chain, $message) {
                        return "Hello {$message}";
                    });
                });
                Filter::apply($class, 'filterable', 'spec.be_prefix');
                Filter::apply($subclass, 'filterable', 'spec.my_prefix');
                expect($subclass::filterable('World!'))->toBe('Hello Be My World!');
            });

            it("applies parent classes's filters using cached filters", function () {
                $class = $this->class;
                $subclass = Double::classname(['extends' => $class]);
                allow($subclass)->toReceive('::filterable')->andRun(function () {
                    return Filter::on(get_called_class(), 'filterable', func_get_args(), function ($chain, $message) {
                        return "Hello {$message}";
                    });
                });
                Filter::apply($class, 'filterable', 'spec.be_prefix');
                Filter::apply($subclass, 'filterable', 'spec.my_prefix');
                expect($subclass::filterable('World!'))->toBe('Hello Be My World!');
                expect($subclass::filterable('World!'))->toBe('Hello Be My World!');
            });

            it("invalidates parent cached filters", function () {
                $class = $this->class;
                $subclass = Double::classname(['extends' => $class]);
                allow($subclass)->toReceive('::filterable')->andRun(function () {
                    return Filter::on(get_called_class(), 'filterable', func_get_args(), function ($chain, $message) {
                        return "Hello {$message}";
                    });
                });
                Filter::apply($class, 'filterable', 'spec.be_prefix');
                Filter::apply($subclass, 'filterable', 'spec.my_prefix');
                expect($subclass::filterable('World!'))->toBe('Hello Be My World!');

                Filter::apply($subclass, 'filterable', 'spec.no_chain');
                expect($subclass::filterable('World!'))->toBe("No Man's My World!");
            });

            it("throws an Exception when trying to apply a filter using an unexisting closure", function () {
                $class = $this->class;
                $closure = function () use ($class) {
                    Filter::apply($class, 'filterable', 'spec.unexisting_closure');
                };
                expect($closure)->toThrow(new Exception('Undefined filter `spec.unexisting_closure`.'));
            });

        });

        describe("::filters()", function () {

            it("exports filters setted as a class level", function () {
                Filter::apply($this->class, 'filterable', 'spec.my_prefix');
                $filters = Filter::filters();
                expect($filters)->toHaveLength(1);
                expect(isset($filters[$this->class]))->toBe(true);
            });

            it("imports class based filters", function () {
                Filter::filters([$this->class => [Filter::registered('spec.my_prefix')]]);
                $filters = Filter::filters();
                expect($filters)->toHaveLength(1);
                expect(isset($filters[$this->class]))->toBe(true);
            });

        });
    });

    describe("::apply()", function () {

        it("throws an Exception when trying to apply a filter on an unfilterable context", function () {
            $closure = function () {
                Filter::apply(null, 'filterable', 'spec.my_prefix');
            };
            expect($closure)->toThrow(new Exception("Error this context can't be filtered."));
        });

    });

    describe("::registered()", function () {

        it("exports the `Filter` class data", function () {
            $registered = Filter::registered();
            expect($registered)->toHaveLength(3);
            Filter::reset();
            Filter::register($registered);
            $registered = Filter::registered();
            expect($registered)->toHaveLength(3);
        });
    });

    describe("::register()", function () {

        it("registers a closure", function () {
            Filter::register('spec.newclosure', function ($chain, $message) {
                $message = "My {$message}";
                return $chain->next($message);
            });
            expect(Filter::registered('spec.newclosure'))->toBe(true);
        });

        it("registers a closure with no name", function () {
            $name = Filter::register(function ($chain, $message) {
                $message = "My {$message}";
                return $chain->next($message);
            });
            expect(Filter::registered($name))->toBe(true);
        });

    });

    describe("::unregister()", function () {

        it("unregisters a closure", function () {
            Filter::register('spec.newclosure', function ($chain, $message) {
                $message = "My {$message}";
                return $chain->next($message);
            });
            Filter::unregister('spec.newclosure');
            expect(Filter::registered('spec.newclosure'))->toBe(false);
        });

    });

    describe("::resets()", function () {

        it("clears all the filters", function () {
            Filter::reset();
            expect(Filter::registered('spec.my_prefix'))->toBe(false);
            expect(Filter::registered('spec.be_prefix'))->toBe(false);
            expect(Filter::registered('spec.no_chain'))->toBe(false);
        });

    });

});
