<?php
namespace Kahlan\Spec\Suite\Matcher;

use stdClass;
use Kahlan\Spec\Mock\Collection;
use Kahlan\Spec\Mock\Traversable;
use Kahlan\Matcher\ToContainKey;

describe("toContainKey", function () {

    describe("::match()", function () {

        context("with an array", function () {

            it("passes when the key is contained", function () {

                expect([1, 2, 3])->toContainKey(2);
                expect(['a' => 1, 'b' => 2, 'c' => 3])->toContainKey('a');
                expect(['a' => null])->toContainKey('a');

            });

            it("passes when the keys are contained", function () {

                expect(['a' => 1, 'b' => 2, 'c' => 3])->toContainKeys('a', 'b');
                expect(['a' => 1, 'b' => 2, 'c' => 3])->toContainKeys(['a', 'b']);

            });

            it("returns `false` when a key is missing", function () {

                expect(['a' => 1, 'b' => 2, 'c' => 3])->not->toContainKey('d');
                expect(['a' => 1, 'b' => 2, 'c' => 3])->not->toContainKeys('a', 'b', 'd');
                expect(['a' => 1, 'b' => 2, 'c' => 3])->not->toContainKeys(['a', 'b', 'd']);

            });

        });

        context("with a collection instance", function () {

            it("passes when the key is contained", function () {

                expect(new Collection(['data' => [1, 2, 3]]))->toContainKey(2);
                expect(new Collection(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->toContainKey('a');
                expect(new Collection(['data' => ['a' => null]]))->toContainKey('a');

            });

            it("passes when the keys are contained", function () {

                expect(new Collection(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->toContainKeys('a', 'b');
                expect(new Collection(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->toContainKeys(['a', 'b']);

            });

            it("returns `false` when a key is missing", function () {

                expect(new Collection(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->not->toContainKey('d');
                expect(new Collection(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->not->toContainKeys('a', 'b', 'd');
                expect(new Collection(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->not->toContainKeys(['a', 'b', 'd']);

            });

        });

        context("with a traversable instance", function () {

            it("passes when the key is contained", function () {

                expect(new Traversable(['data' => [1, 2, 3]]))->toContainKey(2);
                expect(new Traversable(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->toContainKey('a');
                expect(new Traversable(['data' => ['a' => null]]))->toContainKey('a');

            });

            it("passes when the keys are contained", function () {

                expect(new Traversable(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->toContainKeys('a', 'b');
                expect(new Traversable(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->toContainKeys(['a', 'b']);

            });

            it("returns `false` when a key is missing", function () {

                expect(new Traversable(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->not->toContainKey('d');
                expect(new Traversable(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->not->toContainKeys('a', 'd');
                expect(new Traversable(['data' => ['a' => 1, 'b' => 2, 'c' => 3]]))->not->toContainKeys(['a', 'd']);

            });

        });

        it("fails with non array/collection/traversable", function () {

            expect(new stdClass())->not->toContainKey('key');
            expect(false)->not->toContainKey('0');
            expect(true)->not->toContainKey('1');

        });

    });

    describe("::description()", function () {

        it("returns the description message", function () {

            $actual = ToContainKey::description();

            expect($actual)->toBe('contain expected key.');

        });

    });

});
