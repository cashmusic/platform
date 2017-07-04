<?php
namespace Kahlan\Spec\Suite\Cli;

use Kahlan\Cli\CommandLine;

describe("CommandLine", function () {

    describe("->option", function () {

        it("sets an option config", function () {

            $commandLine = new CommandLine();
            $commandLine->option('option1', ['type' => 'boolean']);
            expect($commandLine->option('option1'))->toEqual([
                'type'    => 'boolean',
                'group'   => false,
                'array'   => false,
                'value'   => null,
                'default' => null
            ]);

            $options = $commandLine->options();
            expect($options)->toBeAn('array');
            expect(isset($options['option1']))->toBe(true);
            expect($options['option1'])->toEqual([
                'type'    => 'boolean',
                'group'   => false,
                'array'   => false,
                'value'   => null,
                'default' => null
            ]);

        });

        it("gets the default config", function () {

            $commandLine = new CommandLine();
            expect($commandLine->option('option1'))->toEqual([
                'type'    => 'string',
                'group'   => false,
                'array'   => false,
                'value'   => null,
                'default' => null
            ]);

        });

        it("sets/updates an attribute of an option", function () {

            $commandLine = new CommandLine();
            $commandLine->option('option1', ['type' => 'boolean']);
            expect($commandLine->option('option1'))->toEqual([
                'type'    => 'boolean',
                'group'   => false,
                'array'   => false,
                'value'   => null,
                'default' => null
            ]);

            $commandLine->option('option1', 'default', 'value1');
            expect($commandLine->option('option1'))->toEqual([
                'type'    => 'boolean',
                'group'   => false,
                'array'   => false,
                'value'   => null,
                'default' => 'value1'
            ]);

        });

    });

    describe("->parse()", function () {

        it("parses command line options", function () {

            $commandLine = new CommandLine();
            $actual = $commandLine->parse([
                'command', '--option1', '--option3=value3', '--', '--ingored'
            ]);
            expect($actual)->toEqual([
                'option1' => '',
                'option3' => 'value3'
            ]);

        });

        it("parses command line options with dashed names", function () {

            $commandLine = new CommandLine([
                'double-dashed-option' => ['type' => 'boolean']
            ]);
            $actual = $commandLine->parse([
                'command', '--dashed-option=value', '--double-dashed-option'
            ]);
            expect($actual)->toEqual([
                'dashed-option' => 'value',
                'double-dashed-option' => true
            ]);

        });

        it("provides an array when some multiple occurences of a same option are present", function () {

            $commandLine = new CommandLine(['option1' => ['array' => true]]);
            $actual = $commandLine->parse([
                'command', '--option1', '--option1=value1' , '--option1=value2'
            ]);
            expect($actual)->toEqual([
                'option1' => [
                    '',
                    'value1',
                    'value2'
                ]
            ]);

        });

        it("casts booleans", function () {

            $commandLine = new CommandLine([
                'option1' => ['type' => 'boolean'],
                'option2' => ['type' => 'boolean'],
                'option3' => ['type' => 'boolean'],
                'option4' => ['type' => 'boolean'],
                'option5' => ['type' => 'boolean']
            ]);
            $actual = $commandLine->parse([
                'command', '--option1', '--option2=true' , '--option3=false', '--option4=0'
            ]);
            expect($actual)->toEqual([
                'option1' => true,
                'option2' => true,
                'option3' => false,
                'option4' => false
            ]);

            expect($commandLine->get('option5'))->toBe(false);

        });

        it("casts integers", function () {

            $commandLine = new CommandLine([
                'option'  => ['type' => 'numeric'],
                'option0' => ['type' => 'numeric'],
                'option1' => ['type' => 'numeric'],
                'option2' => ['type' => 'numeric']
            ]);
            $actual = $commandLine->parse([
                'command', '--option', '--option0=0', '--option1=1', '--option2=2'
            ]);
            expect($actual)->toEqual([
                'option' => 1,
                'option0' => 0,
                'option1' => 1,
                'option2' => 2
            ]);

        });

        it("casts string", function () {

            $commandLine = new CommandLine([
                'option1' => ['type' => 'string'],
                'option2' => ['type' => 'string'],
                'option3' => ['type' => 'string'],
                'option4' => ['type' => 'string'],
                'option5' => ['type' => 'string']
            ]);
            $actual = $commandLine->parse([
                'command', '--option1', '--option2=' , '--option3=value'
            ]);
            expect($actual)->toEqual([
                'option1' => null,
                'option2' => '',
                'option3' => 'value'
            ]);

            expect($commandLine->get('option5'))->toBe(null);

        });

        it("provides an array when some multiple occurences of a same option are present", function () {

            $commandLine = new CommandLine([
                'option1:sub1' => ['array' => true],
                'option1:sub3' => ['type' => 'boolean', 'default' => true],
            ]);
            $actual = $commandLine->parse([
                'command', '--option1:sub1', '--option1:sub1=value1', '--option1:sub1=value2', '--option1:sub2=value3'
            ]);
            expect($actual)->toEqual([
                'option1' => [
                    'sub3' => true,
                    'sub1' => [
                        null,
                        'value1',
                        'value2'
                    ],
                    'sub2' => 'value3'
                ]
            ]);

        });

        context("with defaults options", function () {

            it("allows boolean casting", function () {

                $commandLine = new CommandLine([
                    'option1' => ['type' => 'boolean', 'default' => true],
                    'option2' => ['type' => 'boolean', 'default' => false],
                    'option3' => ['type' => 'boolean', 'default' => true],
                    'option4' => ['type' => 'boolean', 'default' => false]
                ]);

                $actual = $commandLine->parse([
                    'command', '--option1', '--option2'
                ]);
                expect($actual)->toEqual([
                    'option1' => true,
                    'option2' => true,
                    'option3' => true,
                    'option4' => false
                ]);

            });

        });

        context("with override set to `false`", function () {

            it("doesn't override existing options when the override params is set to `false`", function () {

                $commandLine = new CommandLine();
                $commandLine->set('option1', 'value1');
                $actual = $commandLine->parse(['--option1=valueX']);
                expect($actual)->toBe(['option1' => 'valueX']);

                $commandLine = new CommandLine();
                $commandLine->set('option1', 'value1');
                $actual = $commandLine->parse(['--option1=valueX'], false);
                expect($actual)->toBe(['option1' => 'value1']);

            });

        });

    });

    describe("->get()", function () {

        it("ignores option value if the value option is set", function () {

            $commandLine = new CommandLine(['option1' => [
                'type'    => 'string',
                'value'   => 'config_value'
            ]]);

            $actual = $commandLine->parse(['command']);
            expect($commandLine->get('option1'))->toEqual('config_value');

            $actual = $commandLine->parse(['command', '--option1']);
            expect($commandLine->get('option1'))->toEqual('config_value');

            $actual = $commandLine->parse(['command', '--option1="some_value"']);
            expect($commandLine->get('option1'))->toEqual('config_value');

        });

        it("formats value according to value function", function () {

            $commandLine = new CommandLine(['option1' => [
                'type'    => 'string',
                'default' => 'default_value',
                'value'   => function ($value, $name, $commandLine) {
                    if (!$value) {
                        return  'empty_value';
                    }
                    if ($value === 'default_value') {
                        return 'default_value';
                    }
                    return 'non_empty_value';
                }
            ]]);

            $actual = $commandLine->parse(['command']);
            expect($commandLine->get('option1'))->toEqual('default_value');

            $actual = $commandLine->parse(['command', '--option1']);
            expect($commandLine->get('option1'))->toEqual('empty_value');

            $actual = $commandLine->parse(['command', '--option1="some_value"']);
            expect($commandLine->get('option1'))->toEqual('non_empty_value');

        });

        it("returns a group subset", function () {

            $commandLine = new CommandLine([
                'option1:sub1' => ['array' => true],
                'option1:sub3' => ['type' => 'boolean', 'default' => true],
            ]);
            $actual = $commandLine->parse([
                'command', '--option1:sub1', '--option1:sub1=value1', '--option1:sub1=value2', '--option1:sub2=value3'
            ]);

            expect($commandLine->get('option1'))->toBe([
                'sub3' => true,
                'sub1' => [
                    null,
                    'value1',
                    'value2'
                ],
                'sub2' => 'value3'
            ]);

        });

        it("returns a group subset even when no explicitly defined", function () {

            $commandLine = new CommandLine();
            $actual = $commandLine->parse([
                'command', '--option1:sub1=value1', '--option1:sub2=value2'
            ]);

            expect($commandLine->get('option1'))->toBe([
                'sub1' => 'value1',
                'sub2' => 'value2'
            ]);

        });

        it("returns an array by default for group subsets", function () {

            $commandLine = new CommandLine(['option1:sub1' => ['array' => true],]);
            $actual = $commandLine->parse(['command']);

            expect($commandLine->get('option1'))->toBe([]);

        });

    });

    describe("->exists()", function () {

        it("returns `true` if the option exists", function () {

            $commandLine = new CommandLine();
            $actual = $commandLine->parse([
                'command', '--option1', '--option2=true' , '--option3=false', '--option4=0'
            ]);
            expect($commandLine->exists('option1'))->toBe(true);
            expect($commandLine->exists('option2'))->toBe(true);
            expect($commandLine->exists('option3'))->toBe(true);
            expect($commandLine->exists('option4'))->toBe(true);
            expect($commandLine->exists('option5'))->toBe(false);

        });

        it("returns `true` if the option as a default value", function () {

            $commandLine = new CommandLine();
            $commandLine->option('option1', ['type' => 'boolean']);
            $commandLine->option('option2', ['type' => 'boolean', 'default' => false]);

            expect($commandLine->exists('option1'))->toBe(false);
            expect($commandLine->exists('option2'))->toBe(true);

        });

    });

    describe("->cast()", function () {

        it("casts array", function () {

            $commandLine = new CommandLine();
            $cast = $commandLine->cast(["some", "string", "and", 10], "string");
            expect($cast)->toBeAn('array');
            foreach ($cast as $c) {
                expect($c)->toBeA('string');
            }

        });

        it("casts boolean", function () {

            $commandLine = new CommandLine();
            $cast = $commandLine->cast(["true", "false", "some_string", null, 10], "boolean");
            expect($cast)->toBeAn('array');
            expect(count($cast))->toBe(5);
            list($bTrue, $bFalse, $string, $null, $number) = $cast;
            expect($bTrue)->toBeA('boolean')->toBe(true);
            expect($bFalse)->toBeA('boolean')->toBe(false);
            expect($string)->toBeA('boolean')->toBe(true);
            expect($null)->toBeA('boolean')->toBe(false);
            expect($number)->toBeA('boolean')->toBe(true);

        });

        it("casts numeric", function () {

            $commandLine = new CommandLine();
            $cast = $commandLine->cast([true, "false", "some_string", null, 10], "numeric");
            expect($cast)->toBeAn('array');
            expect(count($cast))->toBe(5);
            expect(implode($cast))->toBe("100110");

        });

        it("casts value into array", function () {

            $commandLine = new CommandLine();
            $cast = $commandLine->cast("string", "string", true);
            expect($cast)->toBeA("array");
            expect($cast)->toContain("string");

        });

    });

});
