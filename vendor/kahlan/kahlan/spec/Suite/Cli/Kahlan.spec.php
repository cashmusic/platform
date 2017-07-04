<?php
namespace Kahlan\Spec\Suite\Cli;

use stdClass;
use Exception;
use Kahlan\Jit\Interceptor;
use Kahlan\Filter\Filter;
use Kahlan\Suite;
use Kahlan\Matcher;
use Kahlan\Cli\Kahlan;
use Kahlan\Plugin\Quit;

describe("Kahlan", function () {

    /**
     * Save current & reinitialize the Interceptor class.
     */
    beforeAll(function () {
        $this->previous = Interceptor::instance();
        Interceptor::unpatch();
    });

    /**
     * Restore Interceptor class.
     */
    afterAll(function () {
        Interceptor::load($this->previous);
    });

    beforeEach(function () {
        $this->specs = new Kahlan([
            'autoloader' => Interceptor::composer()[0],
            'suite' => new Suite([
                'matcher' => new Matcher()
            ])
        ]);
        $this->console = $this->specs->terminal();
    });

    describe("->autoload()", function () {

        it("gets/sets autoloader", function () {

            $autoloader = new stdClass();
            expect($this->specs->autoloader($autoloader))->toBe($this->specs);
            expect($this->specs->autoloader())->toBe($autoloader);

        });

    });

    describe("->loadConfig()", function () {

        it("sets passed arguments to specs", function () {

            $argv = [
                '--src=src',
                '--spec=spec/Fixture/Kahlan/Spec',
                '--pattern=*MySpec.php',
                '--reporter=verbose',
                '--coverage=3',
                '--config=spec/Fixture/Kahlan/kahlan-config.php',
                '--ff=5',
                '--cc',
                '--no-colors',
                '--no-header',
                '--include=*',
                '--exclude=Kahlan\\',
                '--persistent=false',
                '--autoclear=Kahlan\Plugin\Monkey',
                '--autoclear=Kahlan\Plugin\Call',
                '--autoclear=Kahlan\Plugin\Stub',
                '--autoclear=Kahlan\Plugin\Quit'
            ];

            $this->specs->loadConfig($argv);
            expect($this->specs->commandLine()->get())->toBe([
                'src'        => ['src'],
                'spec'       => ['spec/Fixture/Kahlan/Spec'],
                'pattern'    => "*MySpec.php",
                'reporter'   => [
                    "verbose"
                ],
                'coverage'   => '3',
                'config'     => "spec/Fixture/Kahlan/kahlan-config.php",
                'ff'         => 5,
                'cc'         => true,
                'no-colors'  => true,
                'no-header'  => true,
                'include'    => ['*'],
                'exclude'    => ['Kahlan\\'],
                'persistent' => false,
                'autoclear'  => [
                    'Kahlan\Plugin\Monkey',
                    'Kahlan\Plugin\Call',
                    'Kahlan\Plugin\Stub',
                    'Kahlan\Plugin\Quit'
                ]
            ]);

        });

        it("loads the config file", function () {

            $this->specs->loadConfig([
                '--spec=spec/Fixture/Kahlan/Spec/PassTest.php',
                '--config=spec/Fixture/Kahlan/kahlan-config.php',
                '--pattern=*Test.php',
                '--reporter=none'
            ]);
            $this->specs->run();

            expect($this->specs->suite()->loaded)->toBe(true);

            Interceptor::unpatch();

        });

        it("echoes version if --version if provided", function () {

            skipIf(!$this->console->colors());

            $version = Kahlan::VERSION;

            $expected = <<<EOD
            _     _
  /\ /\__ _| |__ | | __ _ _ __
 / //_/ _` | '_ \| |/ _` | '_ \
/ __ \ (_| | | | | | (_| | | | |
\/  \/\__,_|_| |_|_|\__,_|_| |_|

\033[0;90;49mThe PHP Test Framework for Freedom, Truth and Justice.\033[0m

version \033[0;32;49m{$version}\033[0m

For additional help you must use \033[0;32;49m--help\033[0m


EOD;

            $closure = function () {
                try {
                    $this->specs->loadConfig(['--version']);
                } catch (Exception $e) {
                }
            };

            Quit::disable();
            expect($closure)->toEcho($expected);

        });

        it("echoes the help if --help is provided", function () {

            skipIf(!$this->console->colors());

            $help = <<<EOD
            _     _
  /\ /\__ _| |__ | | __ _ _ __
 / //_/ _` | '_ \| |/ _` | '_ \
/ __ \ (_| | | | | | (_| | | | |
\/  \/\__,_|_| |_|_|\__,_|_| |_|

\033[0;90;49mThe PHP Test Framework for Freedom, Truth and Justice.\033[0m


Usage: kahlan [options]

Configuration Options:

  --config=<file>                     The PHP configuration file to use (default: `'kahlan-config.php'`).
  --src=<path>                        Paths of source directories (default: `['src']`).
  --spec=<path>                       Paths of specification directories (default: `['spec']`).
  --pattern=<pattern>                 A shell wildcard pattern (default: `['*Spec.php', '*.spec.php']`).

Reporter Options:

  --reporter=<name>[:<output_file>]   The name of the text reporter to use, the buit-in text reporters
                                      are `'dot'`, `'bar'`, `'json'`, `'tap'` & `'verbose'` (default: `'dot'`).
                                      You can optionally redirect the reporter output to a file by using the
                                      colon syntax (muliple --reporter options are also supported).

Code Coverage Options:

  --coverage=<integer|string>         Generate code coverage report. The value specify the level of
                                      detail for the code coverage report (0-4). If a namespace, class, or
                                      method definition is provided, it will generate a detailed code
                                      coverage of this specific scope (default `''`).
  --clover=<file>                     Export code coverage report into a Clover XML format.
  --istanbul=<file>                   Export code coverage report into an istanbul compatible JSON format.
  --lcov=<file>                       Export code coverage report into a lcov compatible text format.

Test Execution Options:

  --ff=<integer>                      Fast fail option. `0` mean unlimited (default: `0`).
  --no-colors                         To turn off colors. (default: `false`).
  --no-header                         To turn off header. (default: `false`).
  --include=<string>                  Paths to include for patching. (default: `['*']`).
  --exclude=<string>                  Paths to exclude from patching. (default: `[]`).
  --persistent=<boolean>              Cache patched files (default: `true`).
  --cc                                Clear cache before spec run. (default: `false`).
  --autoclear                         Classes to autoclear after each spec (default: [
                                          `'Kahlan\Plugin\Monkey'`,
                                          `'Kahlan\Plugin\Call'`,
                                          `'Kahlan\Plugin\Stub'`,
                                          `'Kahlan\Plugin\Quit'`
                                      ])

Miscellaneous Options:

  --help                 Prints this usage information.
  --version              Prints Kahlan version

Note: The `[]` notation in default values mean that the related option can accepts an array of values.
To add additionnal values, just repeat the same option many times in the command line.


EOD;

            $closure = function () {
                try {
                    $this->specs->loadConfig(['--help']);
                } catch (Exception $e) {
                }
            };

            Quit::disable();
            expect($closure)->toEcho($help);

        });

        it("doesn't display header with --no-header", function () {

            skipIf(!$this->console->colors());

            $version = Kahlan::VERSION;

            $message = <<<EOD
version \033[0;32;49m{$version}\033[0m

For additional help you must use \033[0;32;49m--help\033[0m


EOD;

            $closure = function () {
                try {
                    $this->specs->loadConfig(['--version', '--no-header']);
                } catch (Exception $e) {
                }
            };

            Quit::disable();
            expect($closure)->toEcho($message);

        });

        it("isolates `kahlan-config.php` execution in a dedicated scope", function () {

            skipIf(!$this->console->colors());

            $version = Kahlan::VERSION;

            $message = <<<EOD
version \033[0;32;49m{$version}\033[0m

For additional help you must use \033[0;32;49m--help\033[0m


EOD;

            $closure = function () {
                try {
                    $this->specs->loadConfig([
                        '--config=spec/Fixture/Kahlan/kahlan-config.php',
                        '--version',
                        '--no-header'
                    ]);
                } catch (Exception $e) {
                }
            };

            Quit::disable();
            expect($closure)->toEcho($message);

        });

        it("doesn't filter empty string from include & exclude", function () {

            $argv = [
                '--include=',
                '--exclude=',
            ];

            $this->specs->loadConfig($argv);
            expect($this->specs->commandLine()->get()['include'])->toBe([]);
            expect($this->specs->commandLine()->get()['exclude'])->toBe([]);

        });

    });

    describe("->run()", function () {

        it("defines the KAHLAN_VERSION constant", function () {

            expect(KAHLAN_VERSION)->toBe(Kahlan::VERSION);

        });

        it("runs a spec which pass", function () {

            $this->specs->loadConfig([
                '--spec=spec/Fixture/Kahlan/Spec/PassTest.php',
                '--pattern=*Test.php',
                '--reporter=none'
            ]);
            $this->specs->run();
            expect($this->specs->suite()->total())->toBe(1);
            expect($this->specs->status())->toBe(0);

            Interceptor::unpatch();

        });

        it("runs a spec which fail", function () {

            $this->specs->loadConfig([
                '--spec=spec/Fixture/Kahlan/Spec/FailTest.php',
                '--pattern=*Test.php',
                '--reporter=none'
            ]);
            $this->specs->run();
            expect($this->specs->suite()->total())->toBe(1);
            expect($this->specs->status())->toBe(-1);

            Interceptor::unpatch();

        });

        it("runs filters in the correct order", function () {

            $this->specs->loadConfig([
                '--spec=spec/Fixture/Kahlan/Spec/PassTest.php',
                '--pattern=*Test.php',
                '--reporter=none'
            ]);

            $autoloader = new stdClass();
            $order = [];

            Filter::register('spec.bootstrap', function ($chain) use (&$order) {
                $order[] = 'bootstrap';
            });
            Filter::apply($this->specs, 'bootstrap', 'spec.bootstrap');

            $previous = $this->previous;
            Filter::register('spec.interceptor', function ($chain) use (&$order, $previous) {
                Interceptor::load($previous);
                $order[] = 'interceptor';
            });
            Filter::apply($this->specs, 'interceptor', 'spec.interceptor');

            Filter::register('spec.namespaces', function ($chain) use (&$order, &$autoloader) {
                $this->autoloader($autoloader);
                $order[] = 'namespaces';
            });
            Filter::apply($this->specs, 'namespaces', 'spec.namespaces');

            Filter::register('spec.patchers', function ($chain) use (&$order) {
                $order[] = 'patchers';
            });
            Filter::apply($this->specs, 'patchers', 'spec.patchers');

            Filter::register('spec.load', function ($chain) use (&$order) {
                $order[] = 'load';
            });
            Filter::apply($this->specs, 'load', 'spec.load');

            Filter::register('spec.reporters', function ($chain) use (&$order) {
                $order[] = 'reporters';
            });
            Filter::apply($this->specs, 'reporters', 'spec.reporters');

            Filter::register('spec.matchers', function ($chain) use (&$order) {
                $order[] = 'matchers';
            });
            Filter::apply($this->specs, 'matchers', 'spec.matchers');

            Filter::register('spec.run', function ($chain) use (&$order) {
                $order[] = 'run';
            });
            Filter::apply($this->specs, 'run', 'spec.run');

            Filter::register('spec.reporting', function ($chain) use (&$order) {
                $order[] = 'reporting';
            });
            Filter::apply($this->specs, 'reporting', 'spec.reporting');

            Filter::register('spec.stop', function ($chain) use (&$order) {
                $order[] = 'stop';
            });
            Filter::apply($this->specs, 'stop', 'spec.stop');

            Filter::register('spec.quit', function ($chain) use (&$order) {
                $order[] = 'quit';
            });
            Filter::apply($this->specs, 'quit', 'spec.quit');

            $this->specs->run();

            expect($order)->toBe([
                'bootstrap',
                'interceptor',
                'namespaces',
                'patchers',
                'load',
                'reporters',
                'matchers',
                'run',
                'reporting',
                'stop',
                'quit'
            ]);

            expect($this->specs->autoloader())->toBe($autoloader);

            Interceptor::unpatch();

        });

    });

});
