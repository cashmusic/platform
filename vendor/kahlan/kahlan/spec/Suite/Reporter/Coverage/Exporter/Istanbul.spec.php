<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

use Kahlan\Reporter\Coverage\Collector;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Driver\Phpdbg;
use Kahlan\Reporter\Coverage\Exporter\Istanbul;
use Kahlan\Spec\Fixture\Reporter\Coverage\NoEmptyLine;
use Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine;
use RuntimeException;

describe("Istanbul", function () {

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

            $time = time();

            $json = Istanbul::export([
                'collector' => $collector,
                'base_path' => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
            ]);
            $ds = DS;

            $expected = <<<EOD
{"\/home\/kahlan\/kahlan\/spec\/Fixture\/Reporter\/Coverage\/NoEmptyLine.php":{"path":"\/home\/kahlan\/kahlan\/spec\/Fixture\/Reporter\/Coverage\/NoEmptyLine.php","s":{"1":1,"2":0,"3":1,"4":0},"f":{"1":1},"b":[],"statementMap":{"1":{"start":{"line":8,"column":0},"end":{"line":8,"column":31}},"2":{"start":{"line":10,"column":0},"end":{"line":10,"column":34}},"3":{"start":{"line":12,"column":0},"end":{"line":12,"column":30}},"4":{"start":{"line":13,"column":0},"end":{"line":13,"column":30}}},"fnMap":{"1":{"name":"shallNotPass","line":6,"loc":{"start":{"line":6,"column":0},"end":{"line":14,"column":false}}}},"branchMap":[]}}
EOD;

            expect(str_replace('\\\\', '\\/', $json))->toBe($expected);
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

            $time = time();

            $json = Istanbul::export([
                'collector' => $collector,
                'base_path' => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
            ]);
            $ds = DS;

            $expected = <<<EOD
{"\/home\/kahlan\/kahlan\/spec\/Fixture\/Reporter\/Coverage\/ExtraEmptyLine.php":{"path":"\/home\/kahlan\/kahlan\/spec\/Fixture\/Reporter\/Coverage\/ExtraEmptyLine.php","s":{"1":1,"2":0,"3":1,"4":0},"f":{"1":1},"b":[],"statementMap":{"1":{"start":{"line":8,"column":0},"end":{"line":8,"column":31}},"2":{"start":{"line":10,"column":0},"end":{"line":10,"column":34}},"3":{"start":{"line":12,"column":0},"end":{"line":12,"column":30}},"4":{"start":{"line":13,"column":0},"end":{"line":13,"column":30}}},"fnMap":{"1":{"name":"shallNotPass","line":6,"loc":{"start":{"line":6,"column":0},"end":{"line":14,"column":false}}}},"branchMap":[]}}
EOD;

            expect(str_replace('\\\\', '\\/', $json))->toBe($expected);

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

            $path = 'spec' . DS . 'Fixture' . DS . 'Reporter' . DS . 'Coverage' . DS . 'NoEmptyLine.php';

            $collector = new Collector([
                'driver' => $this->driver,
                'path'   => $path
            ]);

            $code = new NoEmptyLine();

            $collector->start();
            $code->shallNotPass();
            $collector->stop();

            $time = time();

            $success = Istanbul::write([
                'collector' => $collector,
                'file'      => $this->output,
                'base_path' => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
            ]);

            expect($success)->toBe(629);

            $json = file_get_contents($this->output);
            $ds = DS;

            $expected = <<<EOD
{"\/home\/kahlan\/kahlan\/spec\/Fixture\/Reporter\/Coverage\/NoEmptyLine.php":{"path":"\/home\/kahlan\/kahlan\/spec\/Fixture\/Reporter\/Coverage\/NoEmptyLine.php","s":{"1":1,"2":0,"3":1,"4":0},"f":{"1":1},"b":[],"statementMap":{"1":{"start":{"line":8,"column":0},"end":{"line":8,"column":31}},"2":{"start":{"line":10,"column":0},"end":{"line":10,"column":34}},"3":{"start":{"line":12,"column":0},"end":{"line":12,"column":30}},"4":{"start":{"line":13,"column":0},"end":{"line":13,"column":30}}},"fnMap":{"1":{"name":"shallNotPass","line":6,"loc":{"start":{"line":6,"column":0},"end":{"line":14,"column":false}}}},"branchMap":[]}}
EOD;

            expect(str_replace('\\\\', '\\/', $json))->toBe($expected);

        });

        it("throws exception when no file is set", function () {

            expect(function () {
                Istanbul::write([]);
            })->toThrow(new RuntimeException('Missing file name'));

        });

    });

});
