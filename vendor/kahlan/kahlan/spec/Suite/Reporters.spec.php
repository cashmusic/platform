<?php
namespace Kahlan\Spec\Suite;

use Exception;
use Kahlan\Reporters;
use Kahlan\Plugin\Double;

describe("Reporters", function () {

    beforeEach(function () {
        $this->reporters = new Reporters;
    });

    describe("->add/get()", function () {

        it("stores a reporter", function () {

            $stub = Double::instance();
            $this->reporters->add('my_reporter', $stub);

            $actual = $this->reporters->get('my_reporter');
            expect($actual)->toBe($stub);

        });

        it("throws an exception with a scalar value", function () {

            $closure = function () {
                $this->reporters->add('my_reporter', 'Hello World!');
            };

            expect($closure)->toThrow(new Exception('Error, reporter must be an object.'));

        });

    });

    describe("->get()", function () {

        it("returns `null` for an unexisting reporter", function () {

            $actual = $this->reporters->get('my_reporter');
            expect($actual)->toBe(null);

        });

    });

    describe("->exists()", function () {

        it("returns `true` for an existing reporter", function () {

            $stub = Double::instance();
            $this->reporters->add('my_reporter', $stub);

            $actual = $this->reporters->exists('my_reporter');
            expect($actual)->toBe(true);

        });

        it("returns `false` for an unexisting reporter", function () {

            $actual = $this->reporters->exists('my_reporter');
            expect($actual)->toBe(false);

        });

    });

    describe("->remove()", function () {

        it("removes a reporter", function () {

            $stub = Double::instance();
            $this->reporters->add('my_reporter', $stub);

            $actual = $this->reporters->exists('my_reporter');
            expect($actual)->toBe(true);

            $this->reporters->remove('my_reporter');

            $actual = $this->reporters->exists('my_reporter');
            expect($actual)->toBe(false);

        });

    });

    describe("->clear()", function () {

        it("clears all reporters", function () {

            $stub = Double::instance();
            $this->reporters->add('my_reporter', $stub);

            $actual = $this->reporters->exists('my_reporter');
            expect($actual)->toBe(true);

            $this->reporters->clear();

            $actual = $this->reporters->exists('my_reporter');
            expect($actual)->toBe(false);

        });

    });

    describe("->dispatch()", function () {

        it("runs a method on all reporters", function () {

            $stub1 = Double::instance();
            $this->reporters->add('reporter1', $stub1);

            $stub2 = Double::instance();
            $this->reporters->add('reporter2', $stub2);

            expect($stub1)->toReceive('action')->with(['value']);
            expect($stub2)->toReceive('action')->with(['value']);

            $this->reporters->dispatch('action', ['value']);

        });

    });

});
