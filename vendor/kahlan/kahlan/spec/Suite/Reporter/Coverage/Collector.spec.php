<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

use Kahlan\Reporter\Coverage\Collector;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Driver\Phpdbg;
use Kahlan\Spec\Fixture\Reporter\Coverage\CodeCoverage;

describe("Coverage", function () {

    beforeEach(function () {
        if (!extension_loaded('xdebug') && PHP_SAPI !== 'phpdbg') {
            skipIf(true);
        }
        $this->driver = PHP_SAPI !== 'phpdbg' ? new Xdebug() : new Phpdbg();
    });

    beforeEach(function () {
        $this->path = 'spec/Fixture/Reporter/Coverage/CodeCoverage.php';

        $this->collector = new Collector([
            'driver'    => $this->driver,
            'path'      => $this->path
        ]);

        $this->parent = $this->collector;

        $this->child = new Collector([
            'driver'    => $this->driver,
            'path'      => $this->path
        ]);

    });

    describe("->export()", function () {

        it("exports covered lines", function () {

            $code = new CodeCoverage();

            $this->collector->start();
            $code->shallPass();
            $this->collector->stop();

            $actual = $this->collector->export();

            expect(array_filter(current($actual)))->toBe([
                17 => 1,
                19 => 1,
                21 => 1
            ]);
        });

        it("exports multiline array", function () {

            $code = new CodeCoverage();

            $this->collector->start();
            $code->multilineArrays();
            $this->collector->stop();

            $actual = $this->collector->export();

            expect(array_filter(current($actual)))->toBe([
                34 => 1,
                39 => 1,
                49 => 1,
                54 => 1
            ]);
        });

        it("exports covered lines and append coverage to parent's coverage data", function () {

            $code = new CodeCoverage();

            $this->parent->start();

            $code->shallNotPass();

            $this->child->start();
            $code->shallPass();
            $this->child->stop();

            $this->parent->stop();

            $actual = $this->child->export();
            expect(array_filter(current($actual)))->toBe([
                17 => 1,
                19 => 1,
                21 => 1
            ]);

            $actual = $this->parent->export();
            expect(array_filter(current($actual)))->toBe([
                7 => 1,
                11 => 1,
                17 => 1,
                19 => 1,
                21 => 1
            ]);
        });

        it("exports covered lines and doesn't append coverage to parent's coverage data", function () {

            $code = new CodeCoverage();

            $this->parent->start();

            $code->shallNotPass();

            $this->child->start();
            $code->shallPass();
            $this->child->stop(false);

            $this->parent->stop();

            $actual = $this->child->export();
            expect(array_filter(current($actual)))->toBe([
                17 => 1,
                19 => 1,
                21 => 1
            ]);

            $actual = $this->parent->export();
            expect(array_filter(current($actual)))->toBe([
                7 => 1,
                11 => 1
            ]);
        });

    });

    describe("->start/stop()", function () {

        it("return `true` on success", function () {

            expect($this->collector->start())->toBe(true);
            expect($this->collector->stop())->toBe(true);

        });

    });

    describe("->stop()", function () {

        it("does nothing if not the collector has not been started", function () {

            expect($this->collector->stop())->toBe(false);

        });

        it("does nothing if not the collector has not been started", function () {

            $this->parent->start();
            $this->child->start();

            expect($this->parent->stop())->toBe(false);

            // Required to leave Kahlan in stable state when runned with some coverage reporting.
            $this->child->stop();
            $this->parent->stop();

        });

    });

    describe("->metrics()", function () {

        it("returns the metrics", function () {

            $code = new CodeCoverage();

            $this->collector->start();
            $code->shallPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();
            expect($metrics)->toBeAnInstanceOf('Kahlan\Reporter\Coverage\Metrics');
        });

    });

    describe("->realpath()", function () {

        it("supports special chars", function () {

            $collector = new Collector([
                'driver' => $this->driver,
                'path'   => $this->path,
                'prefix' => '/a/weird~cache/path'
            ]);

            expect($collector->realpath('/a/weird~cache/path/home/user/project/src/filename.php'))->toBe('/home/user/project/src/filename.php');

        });

        it("supports windows volumes", function () {

            $normalize = function ($path) {
                return str_replace('\\', DS, $path);
            };

            $collector = new Collector([
                'driver'    => $this->driver,
                'path'      => $this->path,
                'prefix'    => $normalize('C:\cygwin\tmp\kahlan'),
                'hasVolume' => true
            ]);

            expect($collector->realpath($normalize('C:\cygwin\tmp\kahlan\C\project\src\filename.php')))->toBe($normalize('C:\project\src\filename.php'));

        });

    });

});
