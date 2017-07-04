<?php
namespace Kahlan\Spec\Suite\Plugin\Call;

use Kahlan\Plugin\Call\Calls;

describe("Calls", function () {

    beforeEach(function () {
        Calls::reset();
    });

    describe("::log()", function () {

        it("logs a dynamic call", function () {

            Calls::log('my\name\space\Class', [
                'name' => 'methodName'
            ]);

            $logs = Calls::logs();

            expect($logs[0][0])->toEqual([
                'class'    => 'my\name\space\Class',
                'name'     => 'methodName',
                'instance' => null,
                'static'   => false,
                'method'   => null
            ]);

        });

        it("logs a static call", function () {

            Calls::log('my\name\space\Class', [
                'name' => '::methodName'
            ]);

            $logs = Calls::logs();

            expect($logs[0][0])->toEqual([
                'class'    => 'my\name\space\Class',
                'name'     => 'methodName',
                'instance' => null,
                'static'   => true,
                'method'   => null
            ]);

        });

    });

    describe("::lastFindIndex()", function () {

        it("gets/sets the last find index", function () {

            $index = Calls::lastFindIndex(100);
            expect($index)->toBe(100);

            $index = Calls::lastFindIndex();
            expect($index)->toBe(100);

        });

    });

});
