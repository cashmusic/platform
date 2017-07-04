<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

require 'spec/Fixture/Reporter/Coverage/GlobalFunctions.php';
require 'spec/Fixture/Reporter/Coverage/Functions.php';
use Kahlan\Reporter\Coverage\Collector;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Driver\Phpdbg;
use Kahlan\Spec\Fixture\Reporter\Coverage\NoEmptyLine;
use Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine;
use Kahlan\Spec\Fixture\Reporter\Coverage\ImplementsCoverage;

describe("Metrics", function () {

    beforeEach(function () {
        if (!extension_loaded('xdebug') && PHP_SAPI !== 'phpdbg') {
            skipIf(true);
        }
        $this->driver = PHP_SAPI !== 'phpdbg' ? new Xdebug() : new Phpdbg();
    });

    beforeEach(function () {
        $this->path = [
            'spec/Fixture/Reporter/Coverage/GlobalFunctions.php',
            'spec/Fixture/Reporter/Coverage/Functions.php',
            'spec/Fixture/Reporter/Coverage/ExtraEmptyLine.php',
            'spec/Fixture/Reporter/Coverage/NoEmptyLine.php'
        ];

        $driver = PHP_SAPI !== 'phpdbg' ? new Xdebug() : new Phpdbg();

        $this->collector = new Collector([
            'driver'    => $this->driver,
            'path'      => $this->path
        ]);
    });

    describe("->metrics()", function () {

        it("returns the global metrics", function () {

            $empty = new ExtraEmptyLine();
            $noEmpty = new NoEmptyLine();

            $this->collector->start();
            $empty->shallNotPass();
            $noEmpty->shallNotPass();
            \shallNotPass();
            \Kahlan\Spec\Fixture\Reporter\Coverage\shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();

            $actual = $metrics->data();

            $files = $actual['files'];
            unset($actual['files']);

            expect($actual)->toBe([
                'loc'      => 55,
                'nlloc'    => 39,
                'lloc'     => 16,
                'cloc'     => 8,
                'coverage' => 8,
                'methods'  => 4,
                'cmethods' => 4,
                'percent'  => 50
            ]);

            foreach ($this->path as $path) {
                $path = realpath($path);
                expect(isset($files[$path]))->toBe(true);
            }
        });

        it("returns class metrics", function () {

            $code = new ExtraEmptyLine();

            $this->collector->start();
            $code->shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();

            $actual = $metrics->get('Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine')->data();

            $files = $actual['files'];
            unset($actual['files']);

            expect($actual)->toBe([
                'loc'      => 11,
                'nlloc'    => 7,
                'lloc'     => 4,
                'cloc'     => 2,
                'coverage' => 2,
                'methods'  => 1,
                'cmethods' => 1,
                'percent'  => 50
            ]);

            $path = realpath('spec/Fixture/Reporter/Coverage/ExtraEmptyLine.php');
            expect(isset($files[$path]))->toBe(true);
        });

        it("returns type of metrics", function () {

            $code = new ExtraEmptyLine();

            $this->collector->start();
            $code->shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();
            expect($metrics->type())->toBe('namespace');

        });

        it("returns a parent of metrics", function () {

            $code = new ExtraEmptyLine();

            $this->collector->start();
            $code->shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();
            expect($metrics->parent())->toBe(null);

        });

        it("returns methods metrics", function () {

            $code = new ExtraEmptyLine();

            $this->collector->start();
            $code->shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();

            $actual = $metrics->get('Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine::shallNotPass()')->data();

            $files = $actual['files'];
            unset($actual['files']);

            expect($actual)->toBe([
                'loc'      => 8,
                'nlloc'    => 4,
                'lloc'     => 4,
                'cloc'     => 2,
                'coverage' => 2,
                'methods'  => 1,
                'cmethods' => 1,
                'line'     => [
                    'start' => 5,
                    'stop'  => 13
                ],
                'percent'  => 50
            ]);

            $path = realpath('spec/Fixture/Reporter/Coverage/ExtraEmptyLine.php');
            expect(isset($files[$path]))->toBe(true);
        });

        it("returns global function metrics", function () {

            $this->collector->start();
            \shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();

            $actual = $metrics->get('shallNotPass()')->data();

            $files = $actual['files'];
            unset($actual['files']);

            expect($actual)->toBe([
                'loc'       => 8,
                'nlloc'     => 4,
                'lloc'      => 4,
                'cloc'      => 2,
                'coverage'  => 2,
                'methods'   => 1,
                'cmethods'  => 1,
                'line'      => [
                    'start' => 1,
                    'stop'  => 9
                ],
                'percent'   => 50
            ]);

            $path = realpath('spec/Fixture/Reporter/Coverage/GlobalFunctions.php');
            expect(isset($files[$path]))->toBe(true);
        });

        it("returns function metrics", function () {

            $this->collector->start();
            \Kahlan\Spec\Fixture\Reporter\Coverage\shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();

            $actual = $metrics->get('Kahlan\Spec\Fixture\Reporter\Coverage\shallNotPass()')->data();

            $files = $actual['files'];
            unset($actual['files']);

            expect($actual)->toBe([
                'loc'       => 8,
                'nlloc'     => 4,
                'lloc'      => 4,
                'cloc'      => 2,
                'coverage'  => 2,
                'methods'   => 1,
                'cmethods'  => 1,
                'line'      => [
                    'start' => 3,
                    'stop'  => 11
                ],
                'percent'   => 50
            ]);

            $path = realpath('spec/Fixture/Reporter/Coverage/Functions.php');
            expect(isset($files[$path]))->toBe(true);

        });

        it("returns empty for unknown metric", function () {

            $code = new ExtraEmptyLine();

            $this->collector->start();
            $code->shallNotPass();
            $this->collector->stop();

            $metrics = $this->collector->metrics();
            $actual = $metrics->get('some\unknown\name\space');
            expect($actual)->toBe(null);

        });

        it("ignores interfaces metrics", function () {

            $path = [
                'spec/Fixture/Reporter/Coverage/ImplementsCoverage.php',
                'spec/Fixture/Reporter/Coverage/ImplementsCoverageInterface.php'
            ];

            $collector = new Collector([
                'driver'    => $this->driver,
                'path'      => $path
            ]);

            $code = new ImplementsCoverage();

            $collector->start();
            $code->foo();
            $collector->stop();

            $metrics = $collector->metrics();
            $actual = $metrics->get('Kahlan\Spec\Fixture\Reporter\Coverage\ImplementsCoverage')->data();

            $files = $actual['files'];
            unset($actual['files']);

            expect($actual)->toBe([
                'loc'      => 6,
                'nlloc'    => 5,
                'lloc'     => 1,
                'cloc'     => 1,
                'coverage' => 1,
                'methods'  => 1,
                'cmethods' => 1,
                'percent'  => 100
            ]);

            $path = realpath('spec/Fixture/Reporter/Coverage/ImplementsCoverage.php');
            expect(isset($files[$path]))->toBe(true);

            expect($metrics->get('Kahlan\Spec\Fixture\Reporter\Coverage\ImplementsCoverageInterface'))->toBe(null);

            expect($collector->export())->toBe([
                str_replace('/', DS, 'spec/Fixture/Reporter/Coverage/ImplementsCoverage.php') => [
                    7 => 1
                ]
            ]);

        });

        describe("->children()", function () {

            beforeEach(function () {

                $code = new ExtraEmptyLine();

                $this->collector->start();
                $code->shallNotPass();
                $this->collector->stop();

                $this->metrics = $this->collector->metrics();

            });

            it("returns root's children", function () {

                $children = $this->metrics->children();
                expect(is_array($children))->toBe(true);
                expect(isset($children['Kahlan\\']))->toBe(true);

            });

            it("returns specified child", function () {

                $children = $this->metrics->children('Kahlan\\');
                expect(is_array($children))->toBe(true);
                expect(isset($children['Spec\\']))->toBe(true);

                $children = $this->metrics->children('Kahlan\Spec\\');
                expect(is_array($children))->toBe(true);
                expect(isset($children['Fixture\\']))->toBe(true);

                $children = $this->metrics->children('Kahlan\Spec\Fixture\\');
                expect(is_array($children))->toBe(true);
                expect(isset($children['Reporter\\']))->toBe(true);

                $children = $this->metrics->children('Kahlan\Spec\Fixture\Reporter\\');
                expect(is_array($children))->toBe(true);
                expect(isset($children['Coverage\\']))->toBe(true);

                $children = $this->metrics->children('Kahlan\Spec\Fixture\Reporter\Coverage\\');
                expect(is_array($children))->toBe(true);
                expect(isset($children['ExtraEmptyLine']))->toBe(true);
                expect(isset($children['NoEmptyLine']))->toBe(true);

            });

            it("returns `null` on unknown child", function () {

                $children = $this->metrics->children('unknown_child');
                expect($children)->toBe([]);

            });

        });

    });

});
