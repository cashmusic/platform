<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

use Kahlan\Reporter\Coverage\Collector;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Driver\Phpdbg;
use Kahlan\Reporter\Coverage\Exporter\Coveralls;
use Kahlan\Spec\Fixture\Reporter\Coverage\NoEmptyLine;
use Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine;
use RuntimeException;

describe("Coveralls", function () {

    beforeEach(function () {
        if (!extension_loaded('xdebug') && PHP_SAPI !== 'phpdbg') {
            skipIf(true);
        }
        $this->driver = PHP_SAPI !== 'phpdbg' ? new Xdebug() : new Phpdbg();
    });

    describe("::export()", function () {

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

            $json = Coveralls::export([
                'collector'      => $collector,
                'service_name'   => 'kahlan-ci',
                'service_job_id' => '123',
                'repo_token'     => 'ABC'
            ]);

            $actual = json_decode($json, true);
            unset($actual['run_at']);
            expect($actual['service_name'])->toBe('kahlan-ci');
            expect($actual['service_job_id'])->toBe('123');
            expect($actual['repo_token'])->toBe('ABC');

            $coverage = $actual['source_files'][0];
            expect($coverage['name'])->toBe($path);
            expect($coverage['source'])->toBe(file_get_contents($path));
            expect($coverage['coverage'])->toHaveLength(15);
            expect(array_filter($coverage['coverage']))->toHaveLength(2);

            expect(array_filter($coverage['coverage'], function ($value) {
                return $value === 0;
            }))->toHaveLength(2);

            expect(array_filter($coverage['coverage'], function ($value) {
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

            $json = Coveralls::export([
                'collector'      => $collector,
                'service_name'   => 'kahlan-ci',
                'service_job_id' => '123',
                'repo_token'     => 'ABC'
            ]);

            $actual = json_decode($json, true);
            unset($actual['run_at']);
            expect($actual['service_name'])->toBe('kahlan-ci');
            expect($actual['service_job_id'])->toBe('123');
            expect($actual['repo_token'])->toBe('ABC');

            $coverage = $actual['source_files'][0];
            expect($coverage['name'])->toBe($path);
            expect($coverage['source'])->toBe(file_get_contents($path));
            expect($coverage['coverage'])->toHaveLength(16);

            expect(array_filter($coverage['coverage'], function ($value) {
                return $value === 0;
            }))->toHaveLength(2);

            expect(array_filter($coverage['coverage'], function ($value) {
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

            $success = Coveralls::write([
                'collector'      => $collector,
                'file'           => $this->output,
                'service_name'   => 'kahlan-ci',
                'service_job_id' => '123',
                'repo_token'     => 'ABC'
            ]);

            expect($success)->toBe(585);

            $json = file_get_contents($this->output);
            $actual = json_decode($json, true);

            unset($actual['run_at']);
            expect($actual['service_name'])->toBe('kahlan-ci');
            expect($actual['service_job_id'])->toBe('123');
            expect($actual['repo_token'])->toBe('ABC');

            $coverage = $actual['source_files'][0];
            expect($coverage['name'])->toBe($path);
            expect($coverage['source'])->toBe(file_get_contents($path));
            expect($coverage['coverage'])->toHaveLength(16);

        });

        it("throws an exception no file is set", function () {

            expect(function () {
                Coveralls::write([]);
            })->toThrow(new RuntimeException("Missing file name"));

        });

    });

});
