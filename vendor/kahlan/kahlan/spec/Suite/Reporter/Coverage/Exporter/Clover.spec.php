<?php
namespace Kahlan\Spec\Suite\Reporter\Coverage;

use Kahlan\Reporter\Coverage\Collector;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Driver\Phpdbg;
use Kahlan\Reporter\Coverage\Exporter\Clover;
use Kahlan\Spec\Fixture\Reporter\Coverage\NoEmptyLine;
use Kahlan\Spec\Fixture\Reporter\Coverage\ExtraEmptyLine;
use RuntimeException;

describe("Clover", function () {

    beforeEach(function () {
        if (!extension_loaded('xdebug') && PHP_SAPI !== 'phpdbg') {
            skipIf(true);
        }
        if (!class_exists('DOMDocument', false)) {
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

            $xml = Clover::export([
                'collector' => $collector,
                'time'      => $time,
                'base_path' => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
            ]);
            $ds = DS;

            $expected = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="{$time}">
  <project timestamp="{$time}">
    <file name="{$ds}home{$ds}kahlan{$ds}kahlan{$ds}spec{$ds}Fixture{$ds}Reporter{$ds}Coverage{$ds}NoEmptyLine.php">
      <line num="8" type="stmt" count="1"/>
      <line num="10" type="stmt" count="0"/>
      <line num="12" type="stmt" count="1"/>
      <line num="13" type="stmt" count="0"/>
    </file>
    <metrics loc="15" ncloc="11" statements="4" coveredstatements="2"/>
  </project>
</coverage>

EOD;

            expect($xml)->toBe($expected);
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

            $xml = Clover::export([
                'collector' => $collector,
                'time'      => $time,
                'base_path' => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
            ]);
            $ds = DS;

            $expected = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="{$time}">
  <project timestamp="{$time}">
    <file name="{$ds}home{$ds}kahlan{$ds}kahlan{$ds}spec{$ds}Fixture{$ds}Reporter{$ds}Coverage{$ds}ExtraEmptyLine.php">
      <line num="8" type="stmt" count="1"/>
      <line num="10" type="stmt" count="0"/>
      <line num="12" type="stmt" count="1"/>
      <line num="13" type="stmt" count="0"/>
    </file>
    <metrics loc="16" ncloc="12" statements="4" coveredstatements="2"/>
  </project>
</coverage>

EOD;

            expect($xml)->toBe($expected);

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

            $success = Clover::write([
                'collector' => $collector,
                'file'      => $this->output,
                'time'      => $time,
                'base_path' => DS . 'home' . DS . 'kahlan' . DS . 'kahlan'
            ]);

            expect($success)->toBe(481);

            $xml = file_get_contents($this->output);
            $ds = DS;

            $expected = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="{$time}">
  <project timestamp="{$time}">
    <file name="{$ds}home{$ds}kahlan{$ds}kahlan{$ds}spec{$ds}Fixture{$ds}Reporter{$ds}Coverage{$ds}NoEmptyLine.php">
      <line num="8" type="stmt" count="1"/>
      <line num="10" type="stmt" count="0"/>
      <line num="12" type="stmt" count="1"/>
      <line num="13" type="stmt" count="0"/>
    </file>
    <metrics loc="15" ncloc="11" statements="4" coveredstatements="2"/>
  </project>
</coverage>

EOD;

            expect($xml)->toBe($expected);

        });

        it("throws exception when no file is set", function () {

            expect(function () {
                Clover::write([]);
            })->toThrow(new RuntimeException('Missing file name'));

        });

    });

});
