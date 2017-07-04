<?php
namespace Kahlan\Spec\Suite\Filter;

use Kahlan\Filter\MethodFilters;

describe('MethodFilters', function () {

    beforeEach(function () {
        $this->methodFilters = new MethodFilters();
    });

    describe("->apply/filters()", function () {

        it("applies a filter to a method", function () {

            $this->methodFilters->apply('myMethod', 'spec.hello_world', function () {
                return 'Hello World!';
            });

            $filters = $this->methodFilters->filters('myMethod');
            expect($filters)->toBeAn('array')->toHaveLength(1);

            $closure = reset($filters);
            expect($closure())->toBe('Hello World!');

        });

        it("applies multiple filters to a method", function () {

            $this->methodFilters->apply('myMethod', 'spec.hello_world', function () {
                return 'Hello World!';
            });
            $this->methodFilters->apply('myMethod', 'spec.hello_beautiful_world', function () {
                return 'Hello Beautiful World!';
            });

            $filters = $this->methodFilters->filters('myMethod');
            expect($filters)->toBeAn('array')->toHaveLength(2);

            $closure = reset($filters);
            expect($closure())->toBe('Hello World!');

            $closure = next($filters);
            expect($closure())->toBe('Hello Beautiful World!');

        });

        it("applies a filter to many methods once", function () {

            $this->methodFilters->apply(['myMethod1', 'myMethod2'], 'spec.hello_boy', function () {
                return 'Hello Boy!';
            });

            foreach (['myMethod1', 'myMethod2'] as $method) {
                $filters = $this->methodFilters->filters($method);
                expect($filters)->toBeAn('array')->toHaveLength(1);

                $closure = reset($filters);
                expect($closure())->toBe('Hello Boy!');
            }

        });

    });

    describe("->detach()", function () {

        it("detaches a filter", function () {

            $this->methodFilters->apply('myMethod', 'spec.hello_world', function () {
                return 'Hello World!';
            });
            $this->methodFilters->detach('myMethod', 'spec.hello_world');

            $filters = $this->methodFilters->filters('myMethod');
            expect($filters)->toHaveLength(0);

        });

        it("detaches filters by name", function () {

            $this->methodFilters->apply(['myMethod1', 'myMethod2'], 'spec.hello_boy', function () {
                return 'Hello Boy!';
            });
            $this->methodFilters->detach(null, 'spec.hello_boy');

            $filters = $this->methodFilters->filters('myMethod1');
            expect($filters)->toHaveLength(0);

            $filters = $this->methodFilters->filters('myMethod2');
            expect($filters)->toHaveLength(0);

        });

        it("detaches all filters of a method", function () {

            $this->methodFilters->apply('myMethod', 'spec.hello_world', function () {
                return 'Hello World!';
            });
            $this->methodFilters->apply('myMethod', 'spec.hello_beautiful_world', function () {
                return 'Hello Beautiful World!';
            });
            $this->methodFilters->detach('myMethod');

            $filters = $this->methodFilters->filters('myMethod');
            expect($filters)->toHaveLength(0);

        });

        it("detaches all filters", function () {

            $this->methodFilters->apply('myMethod', 'spec.hello_world', function () {
                return 'Hello World!';
            });
            $this->methodFilters->apply('myMethod', 'spec.hello_beautiful_world', function () {
                return 'Hello Beautiful World!';
            });
            $this->methodFilters->apply(['myMethod1', 'myMethod2'], 'spec.hello_boy', function () {
                return 'Hello Boy!';
            });
            $this->methodFilters->detach();

            $filters = $this->methodFilters->filters('myMethod');
            expect($filters)->toHaveLength(0);

            $filters = $this->methodFilters->filters('myMethod1');
            expect($filters)->toHaveLength(0);

            $filters = $this->methodFilters->filters('myMethod2');
            expect($filters)->toHaveLength(0);

        });

        it("clears all filters", function () {

            $this->methodFilters->apply('myMethod', 'spec.hello_world', function () {
                return 'Hello World!';
            });
            $this->methodFilters->apply('myMethod', 'spec.hello_beautiful_world', function () {
                return 'Hello Beautiful World!';
            });
            $this->methodFilters->apply(['myMethod1', 'myMethod2'], 'spec.hello_boy', function () {
                return 'Hello Boy!';
            });
            $this->methodFilters->clear();

            $filters = $this->methodFilters->filters('myMethod');
            expect($filters)->toHaveLength(0);

            $filters = $this->methodFilters->filters('myMethod1');
            expect($filters)->toHaveLength(0);

            $filters = $this->methodFilters->filters('myMethod2');
            expect($filters)->toHaveLength(0);

        });

    });

});
