<?php
namespace box\spec\suite;

use Kahlan\Plugin\Stub;

use stdClass;
use Kahlan\Box\Box;
use Kahlan\Box\Wrapper;
use Kahlan\Box\BoxException;

describe("Wrapper", function () {

    beforeEach(function () {
            $this->box = new Box();
    });

    describe("->__construct()", function () {

        it("throws an exception if the `'box'` parameter is empty", function () {

            $closure = function () {
                $wrapper = new Wrapper(['box' => null, 'name' => 'spec.stdClass']);
            };

            expect($closure)->toThrow(new BoxException("Error, the wrapper require at least `'box'` & `'name'` to not be empty."));

        });

        it("throws an exception if the `'name'` parameter is empty", function () {

            $this->box->factory('spec.stdClass', function () {
                return new stdClass;
            });

            $closure = function () {
                $wrapper = new Wrapper(['box' => $this->box, 'name' => '']);
            };

            expect($closure)->toThrow(new BoxException("Error, the wrapper require at least `'box'` & `'name'` to not be empty."));

        });

    });

    describe("->get()", function () {

        it("resolve a dependency", function () {

            $this->box->factory('spec.stdClass', function () {
                return new stdClass;
            });
            $wrapper = new Wrapper(['box' => $this->box, 'name' => 'spec.stdClass']);

            $dependency = $wrapper->get();
            expect($dependency)->toBeAnInstanceOf("stdClass");

            expect($wrapper->get())->toBe($dependency);

        });

        it("throws an exception if the dependency doesn't exists", function () {

            $wrapper = new Wrapper(['box' => $this->box, 'name' => 'spec.stdUnexistingClass']);
            expect(function () use ($wrapper) {
                $wrapper->get();
            })->toThrow(new BoxException());

        });

        it("passes parameters to the Closure", function () {

            $this->box->factory('spec.arguments', function () {
                return func_get_args();
            });
            $params = [
                'param1',
                'param2'
            ];
            $wrapper = new Wrapper([
                'box'    => $this->box,
                'name'   => 'spec.arguments',
                'params' => $params
            ]);
            expect($wrapper->get())->toBe($params);

        });

        it("override passed parameters to the Closure", function () {

            $this->box->factory('spec.arguments', function () {
                return func_get_args();
            });
            $params = [
                'param1',
                'param2'
            ];
            $wrapper = new Wrapper([
                'box'    => $this->box,
                'name'   => 'spec.arguments',
                'params' => $params
            ]);
            $overrided = [
                'param3',
                'param4'
            ];
            expect($wrapper->get('param3', 'param4'))->toBe($overrided);

        });

    });

});
