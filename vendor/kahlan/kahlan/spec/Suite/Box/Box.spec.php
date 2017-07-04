<?php
namespace box\spec\suite;

use stdClass;
use Kahlan\Box\Box;
use Kahlan\Box\BoxException;

class MyTestClass
{
    public $params = [];

    public function __construct()
    {
        $this->params = func_get_args();
    }
}

describe("Box", function () {

    describe("->factory()", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("binds a closure", function () {

            $this->box->factory('spec.stdClass', function () {
                return new stdClass;
            });
            expect($this->box->get('spec.stdClass'))->toBeAnInstanceOf("stdClass");

        });

        it("binds a classname", function () {

            $this->box->factory('spec.stdClass', "stdClass");
            expect($this->box->get('spec.stdClass'))->toBeAnInstanceOf("stdClass");

        });

        it("passes all arguments to the Closure", function () {

            $this->box->factory('spec.arguments', function () {
                return func_get_args();
            });
            $params = [
                'params1',
                'params2'
            ];
            expect($this->box->get('spec.arguments', $params[0],  $params[1]))->toBe($params);

        });

        it("passes all arguments to the constructor", function () {

            $this->box->factory('spec.arguments', 'box\spec\suite\MyTestClass');
            $params = [
                'params1',
                'params2'
            ];
            expect($this->box->get('spec.arguments', $params[0],  $params[1])->params)->toBe($params);

        });

        it("creates different instances", function () {

            $this->box->factory('spec.stdClass', "stdClass");

            $instance1 = $this->box->get('spec.stdClass');
            $instance2 = $this->box->get('spec.stdClass');
            expect($instance1)->not->toBe($instance2);

        });

        it("throws an exception if the definition is not a string or a Closure", function () {

            $expected = new BoxException("Error `spec.instance` is not a closure definition dependency can't use it as a factory definition.");

            $closure = function () {
                $this->box->factory('spec.instance', new stdClass);
            };
            expect($closure)->toThrow($expected);

            $closure = function () {
                $this->box->factory('spec.instance', []);
            };
            expect($closure)->toThrow($expected);

        });

    });

    describe("->service()", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("shares a string", function () {

            $this->box->service('spec.stdClass', "stdClass");
            expect($this->box->get('spec.stdClass'))->toBe("stdClass");

        });

        it("shares an instance", function () {

            $instance = new stdClass;
            $this->box->service('spec.instance', $instance);
            expect($this->box->get('spec.instance'))->toBe($instance);

        });

        it("gets the same instance", function () {

            $this->box->service('spec.stdClass', new stdClass);
            $instance1 = $this->box->get('spec.stdClass');
            $instance2 = $this->box->get('spec.stdClass');
            expect($instance1)->toBe($instance2);

        });

        it("shares a singleton using the closure syntax", function () {

            $this->box->service('spec.stdClass', function () {
                return new stdClass;
            });
            $instance1 = $this->box->get('spec.stdClass');
            $instance2 = $this->box->get('spec.stdClass');
            expect($instance1)->toBe($instance2);
            expect($instance1)->toBeAnInstanceOf("stdClass");

        });

        it("shares a closure", function () {

            $closure = function () {
                return "Hello World!";
            };
            $this->box->service('spec.closure', function () use ($closure) {
                return $closure;
            });

            $closure1 = $this->box->get('spec.closure');
            $closure2 = $this->box->get('spec.closure');
            expect($closure1)->toBe($closure2);
            expect($closure1)->toBeAnInstanceOf("Closure");
            expect($closure1())->toBe("Hello World!");

        });

    });

    describe("has", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("returns `false` if the Box is empty", function () {
            expect($this->box->has('spec.hello'))->toBe(false);
        });

        it("returns `true` if the Box contain the bind dependency", function () {
            $this->box->factory('spec.stdClass', function () {
                return new stdClass;
            });
            expect($this->box->has('spec.stdClass'))->toBe(true);
        });

        it("returns `true` if the Box contain the share dependency", function () {
            $this->box->service('spec.hello', "Hello World!");
            expect($this->box->has('spec.hello'))->toBe(true);
        });
    });

    describe("->get()", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("throws an exception if the dependency doesn't exists", function () {

            $closure = function () {
                $this->box->get('spec.stdUnexistingClass');
            };
            expect($closure)->toThrow(new BoxException("Unexisting `spec.stdUnexistingClass` definition dependency."));

        });
    });

    describe("->wrap()", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("returns a dependency container", function () {

            $this->box->factory('spec.stdClass', function () {
                return new stdClass;
            });
            $wrapper = $this->box->wrap('spec.stdClass');
            expect($wrapper)->toBeAnInstanceOf('Kahlan\Box\Wrapper');

            $dependency = $wrapper->get();
            expect($dependency)->toBeAnInstanceOf("stdClass");

            expect($wrapper->get())->toBe($dependency);

        });

        it("throws an exception if the dependency definition is not a closure doesn't exists", function () {

            $closure = function () {
                $this->box->service('spec.stdClass', new stdClass);
                $wrapper = $this->box->wrap('spec.stdClass');
            };

            expect($closure)->toThrow(new BoxException("Error `spec.stdClass` is not a closure definition dependency can't be wrapped."));

        });

        it("throws an exception if the dependency doesn't exists", function () {

            $closure = function () {
                $this->box->wrap('spec.stdUnexistingClass');
            };

            expect($closure)->toThrow(new BoxException("Unexisting `spec.stdUnexistingClass` definition dependency."));

        });
    });

    describe("->remove()", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("remove a bind", function () {

            $this->box->factory('spec.stdClass', function () {
                return new stdClass;
            });
            expect($this->box->has('spec.stdClass'))->toBe(true);

            $this->box->remove('spec.stdClass');
            expect($this->box->has('spec.stdClass'))->toBe(false);

        });

    });

    describe("->clear()", function () {

        beforeEach(function () {
            $this->box = new Box();
        });

        it("clears all binds & shares", function () {

            $this->box->factory('spec.stdClass', "stdClass");
            $this->box->service('spec.hello', "Hello World!");
            expect($this->box->has('spec.stdClass'))->toBe(true);
            expect($this->box->has('spec.hello'))->toBe(true);

            $this->box->clear();
            expect($this->box->has('spec.stdClass'))->toBe(false);
            expect($this->box->has('spec.hello'))->toBe(false);

            $closure = function () {
                $this->box->get('spec.stdClass');
            };
            expect($closure)->toThrow(new BoxException("Unexisting `spec.stdClass` definition dependency."));

            $closure = function () {
                $this->box->get('spec.hello');
            };
            expect($closure)->toThrow(new BoxException("Unexisting `spec.hello` definition dependency."));

        });

    });

});

describe("box()", function () {

    beforeEach(function () {
        \Kahlan\box(false);
    });

    it("adds a box", function () {

        $box = new Box();
        $actual = \Kahlan\box('box.spec', $box);

        expect($actual)->toBe($box);
    });

    it("gets a box", function () {

        $box = new Box();
        \Kahlan\box('box.spec', $box);
        $actual = \Kahlan\box('box.spec');

        expect($actual)->toBe($box);
    });

    it("adds a default box", function () {

        $box = new Box();

        expect(\Kahlan\box($box))->toBe($box);
        expect(\Kahlan\box())->toBe($box);

    });

    it("gets a default box", function () {

        $box = \Kahlan\box();
        expect($box)->toBeAnInstanceOf('Kahlan\Box\Box');
        expect(\Kahlan\box())->toBe($box);

    });

    it("removes a box", function () {

        $box = new Box();
        \Kahlan\box('box.spec', $box);
        \Kahlan\box('box.spec', false);

        $closure = function () {
            \Kahlan\box('box.spec');
        };
        expect($closure)->toThrow(new BoxException("Unexisting box `'box.spec'`."));
    });

    it("removes all boxes", function () {

        $box = new Box();
        \Kahlan\box('box.spec1', $box);
        \Kahlan\box('box.spec2', $box);
        \Kahlan\box(false);

        $closure = function () {
            \Kahlan\box('box.spec1');
        };
        expect($closure)->toThrow(new BoxException("Unexisting box `'box.spec1'`."));

        $closure = function () {
            \Kahlan\box('box.spec2');
        };
        expect($closure)->toThrow(new BoxException("Unexisting box `'box.spec2'`."));
    });

    it("throws an exception when trying to get an unexisting box", function () {
        $closure = function () {
            \Kahlan\box('box.spec');
        };
        expect($closure)->toThrow(new BoxException("Unexisting box `'box.spec'`."));
    });

});
