<?php
namespace Kahlan\Spec\Suite;

use Exception;
use stdClass;
use DateTime;
use SplMaxHeap;
use Kahlan\Specification;
use Kahlan\Matcher;
use Kahlan\Plugin\Double;

describe("Matcher", function () {

    beforeEach(function () {
        $this->matchers = Matcher::get();
    });

    afterEach(function () {
        Matcher::reset();
        foreach ($this->matchers as $name => $value) {
            foreach ($value as $for => $class) {
                Matcher::register($name, $class, $for);
            }
        }
    });

    describe("::register()", function () {

        it("registers a matcher", function () {

            Matcher::register('toBeOrNotToBe', Double::classname(['extends' => 'Kahlan\Matcher\ToBe']));
            expect(Matcher::exists('toBeOrNotToBe'))->toBe(true);
            expect(Matcher::exists('toBeOrNot'))->toBe(false);

            expect(true)->toBeOrNotToBe(true);

        });

        it("registers a matcher for a specific class", function () {

            Matcher::register('toEqualCustom', Double::classname(['extends' => 'Kahlan\Matcher\ToEqual']), 'stdClass');
            expect(Matcher::exists('toEqualCustom', 'stdClass'))->toBe(true);
            expect(Matcher::exists('toEqualCustom'))->toBe(false);

            expect(new stdClass())->toEqualCustom(new stdClass());
            expect(new stdClass())->not->toEqualCustom(new DateTime());

        });

        it("makes registered matchers for a specific class available for sub classes", function () {

            Matcher::register('toEqualCustom', Double::classname(['extends' => 'Kahlan\Matcher\ToEqual']), 'SplHeap');
            expect(Matcher::exists('toEqualCustom', 'SplHeap'))->toBe(true);
            expect(Matcher::exists('toEqualCustom'))->toBe(false);

            expect(new SplMaxHeap())->toEqualCustom(new SplMaxHeap());

        });

    });

    describe("::get()", function () {

        it("returns all registered matchers", function () {

            Matcher::reset();
            Matcher::register('toBe', 'Kahlan\Matcher\ToBe');

            expect(Matcher::get())->toBe([
                'toBe' => ['' => 'Kahlan\Matcher\ToBe']
            ]);

        });

        it("returns a registered matcher", function () {

            expect(Matcher::get('toBe'))->toBe('Kahlan\Matcher\ToBe');

        });

        it("returns all registered matchers for a specific matcher", function () {

            Matcher::register('toBe', 'Kahlan\Matcher\ToEqual', 'stdClass');

            expect(Matcher::get('toBe', true))->toBe([
                ''         => 'Kahlan\Matcher\ToBe',
                'stdClass' => 'Kahlan\Matcher\ToEqual'
            ]);

        });

        it("returns the default registered matcher", function () {

            expect(Matcher::get('toBe', 'stdClass'))->toBe('Kahlan\Matcher\ToBe');

        });

        it("returns a custom matcher when defined for a specific class", function () {

            Matcher::register('toBe', 'Kahlan\Matcher\ToEqual', 'stdClass');

            expect(Matcher::get('toBe', 'DateTime'))->toBe('Kahlan\Matcher\ToBe');
            expect(Matcher::get('toBe', 'stdClass'))->toBe('Kahlan\Matcher\ToEqual');

        });

        it("throws an exception when using an undefined matcher name", function () {

            $closure = function () {
                Matcher::get('toHelloWorld');
            };

            expect($closure)->toThrow(new Exception("Unexisting default matcher attached to `'toHelloWorld'`."));

        });

        it("throws an exception when using an undefined matcher name for a specific class", function () {

            $closure = function () {
                Matcher::get('toHelloWorld', 'stdClass');
            };

            expect($closure)->toThrow(new Exception("Unexisting matcher attached to `'toHelloWorld'` for `stdClass`."));

        });

    });

    describe("::unregister()", function () {

        it("unregisters a matcher", function () {

            Matcher::register('toBeOrNotToBe', Double::classname(['extends' => 'Kahlan\Matcher\ToBe']));
            expect(Matcher::exists('toBeOrNotToBe'))->toBe(true);

            Matcher::unregister('toBeOrNotToBe');
            expect(Matcher::exists('toBeOrNotToBe'))->toBe(false);

        });

        it("unregisters all matchers", function () {

            expect(Matcher::get())->toBeGreaterThan(1);
            Matcher::unregister(true);
            Matcher::register('toHaveLength', 'Kahlan\Matcher\ToHaveLength');
            expect(Matcher::get())->toHaveLength(1);

        });

    });

    describe("::reset()", function () {

         it("unregisters all matchers", function () {

            expect(Matcher::get())->toBeGreaterThan(1);
            Matcher::reset();
            Matcher::register('toHaveLength', 'Kahlan\Matcher\ToHaveLength');
            expect(Matcher::get())->toHaveLength(1);

         });

    });

});
