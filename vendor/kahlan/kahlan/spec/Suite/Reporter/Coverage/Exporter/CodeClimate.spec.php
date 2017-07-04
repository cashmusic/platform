<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

use Kahlan\Reporter\Coverage\Collector;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Driver\Phpdbg;
use Kahlan\Reporter\Coverage\Exporter\CodeClimate;
use Kahlan\Spec\Fixture\Reporter\Coverage\NoEmptyLine;
use Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine;
use RuntimeException;

describe("CodeClimate", function () {

    beforeEach(function () {
        if (!extension_loaded('xdebug') && PHP_SAPI !== 'phpdbg') {
            skipIf(true);
        }
        $this->driver = PHP_SAPI !== 'phpdbg' ? new Xdebug() : new Phpdbg();
    });

    describe("::export()", function () {

        it("exports custom parameters", function () {

            $collector = new Collector([
                'driver' => $this->driver
            ]);

            $json = CodeClimate::export([
                'collector'    => $collector,
                'repo_token'   => 'ABC',
                'head'         => '1234',
                'branch'       => 'mybranch',
                'committed_at' => '1419462000',
                'ci_service'   => [
                    'name'             => 'kahlan-ci',
                    'build_identifier' => '123'
                ]
            ]);

            $actual = json_decode($json, true);

            expect($actual['repo_token'])->toBe('ABC');

            expect($actual['git'])->toBe([
                'head'         => '1234',
                'branch'       => 'mybranch',
                'committed_at' => '1419462000'
            ]);

            expect($actual['ci_service'])->toBe([
                'name'             => 'kahlan-ci',
                'build_identifier' => '123'
            ]);
        });

        it("exports the coverage of a file with no extra end line", function () {

            $path = 'spec' . DS . 'Fixture' . DS . 'Reporter' . DS . 'Coverage' . DS . 'NoEmptyLine.php';

            $collector = new Collector([
                'driver' => $this->driver,
                'path'   => $path
            ]);

            $code = new NoEmptyLine();

            $collector->start();
            $code->shallNotPass();
            $collector->stop();

            $json = CodeClimate::export([
                'collector'  => $collector,
                'repo_token' => 'ABC'
            ]);

            $actual = json_decode($json, true);

            $coverage = $actual['source_files'][0];
            expect($coverage['name'])->toBe($path);

            $coverage = json_decode($coverage['coverage']);
            expect($coverage)->toHaveLength(15);

            expect(array_filter($coverage))->toHaveLength(2);

            expect(array_filter($coverage, function ($value) {
                return $value === 0;
            }))->toHaveLength(2);

            expect(array_filter($coverage, function ($value) {
                return $value === null;
            }))->toHaveLength(11);

        });

        it("exports the coverage of a file with an extra line at the end", function () {

            $path = 'spec' . DS . 'Fixture' . DS . 'Reporter' . DS . 'Coverage' . DS . 'ExtraEmptyLine.php';

            $collector = new Collector([
                'driver' => $this->driver,
                'path'   => $path
            ]);

            $code = new ExtraEmptyLine();

            $collector->start();
            $code->shallNotPass();
            $collector->stop();

            $json = CodeClimate::export([
                'collector'  => $collector,
                'repo_token' => 'ABC',
                'ci'         => [
                    'name'             => 'kahlan-ci',
                    'build_identifier' => '123'
                ]
            ]);

            $actual = json_decode($json, true);

            $coverage = $actual['source_files'][0];
            expect($coverage['name'])->toBe($path);

            $coverage = json_decode($coverage['coverage']);
            expect($coverage)->toHaveLength(16);

            expect(array_filter($coverage, function ($value) {
                return $value === 0;
            }))->toHaveLength(2);

            expect(array_filter($coverage, function ($value) {
                return $value === null;
            }))->toHaveLength(12);

        });

    });

    describe("::write()", function () {

        beforeEach(function () {
            $this->output = tempnam(sys_get_temp_dir(), "KAHLAN");
        });

        afterEach(function () {
            unlink($this->output);
        });

        it("writes the coverage to a file", function () {

            $path = 'spec' . DS . 'Fixture' . DS . 'Reporter' . DS . 'Coverage' . DS . 'ExtraEmptyLine.php';

            $collector = new Collector([
                'driver' => $this->driver,
                'path'   => $path
            ]);

            $code = new ExtraEmptyLine();

            $collector->start();
            $code->shallNotPass();
            $collector->stop();

            $success = CodeClimate::write([
                'collector'   => $collector,
                'file'        => $this->output,
                'environment' => [
                    'pwd'     => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
                ],
                'repo_token'  => 'ABC'
            ]);

            $json = file_get_contents($this->output);
            expect($success)->toBe(strlen($json));

            $actual = json_decode($json, true);

            $coverage = $actual['source_files'][0];
            expect($coverage['name'])->toBe($path);
            $coverage = json_decode($coverage['coverage']);
            expect($coverage)->toHaveLength(16);

        });

        it("throws an exception when no file is set", function () {

            expect(function () {
                CodeClimate::write([]);
            })->toThrow(new RuntimeException("Missing file name"));

        });

    });

});
