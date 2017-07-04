<?php
namespace Kahlan\Cli {

    use Kahlan\Dir\Dir;
    use Kahlan\Jit\Interceptor;
    use Kahlan\Filter\Filter;
    use Kahlan\Filter\Behavior\Filterable;
    use Kahlan\Matcher;
    use Kahlan\Jit\Patcher\Pointcut;
    use Kahlan\Jit\Patcher\Monkey;
    use Kahlan\Jit\Patcher\Rebase;
    use Kahlan\Jit\Patcher\Quit;
    use Kahlan\Plugin\Quit as QuitStatement;
    use Kahlan\Reporters;
    use Kahlan\Reporter\Terminal;
    use Kahlan\Reporter\Coverage;
    use Kahlan\Reporter\Coverage\Driver\Phpdbg;
    use Kahlan\Reporter\Coverage\Driver\Xdebug;
    use Kahlan\Reporter\Coverage\Exporter\Clover;
    use Kahlan\Reporter\Coverage\Exporter\Istanbul;
    use Kahlan\Reporter\Coverage\Exporter\Lcov;
    use Composer\Script\Event;

    class Kahlan
    {
        use Filterable;

        const VERSION = '3.1.16';

        /**
         * Starting time.
         *
         * @var float
         */
        protected $_start = 0;

        /**
         * The suite instance.
         *
         * @var object
         */
        protected $_suite = null;

        /**
         * The runtime autoloader.
         *
         * @var object
         */
        protected $_autoloader = null;

        /**
         * The reporter container.
         *
         * @var object
         */
        protected $_reporters = null;

        /**
         * The arguments.
         *
         * @var object
         */
        protected $_commandLine = null;

        /**
         * Warning !
         * This method should only be called by Composer as an attempt to auto clear up caches automatically
         * when the version of Kahlan is updated.
         * It will have no effect if the cache location is changed the default config file (i.e. `'kahlan-config.php'`).
         */
        public static function composerPostUpdate(Event $event)
        {
            if (!defined('DS')) {
                define('DS', DIRECTORY_SEPARATOR);
            }
            $kahlan = new static(['autoloader' => $event->getComposer()]);
            $kahlan->loadConfig();
            $kahlan->_interceptor();
            if ($interceptor = Interceptor::instance()) {
                $interceptor->clearCache();
            }
        }

        /**
         * The Constructor.
         *
         * @param array $options Possible options are:
         *                       - `'autoloader'` _object_ : The autoloader instance.
         *                       - `'suite'`      _object_ : The suite instance.
         */
        public function __construct($options = [])
        {
            $defaults = ['autoloader' => null, 'suite' => null];
            $options += $defaults;

            $this->_autoloader = $options['autoloader'];
            $this->_suite = $options['suite'];

            $this->_reporters = new Reporters();
            $this->_commandLine = $commandLine = new CommandLine();

            $commandLine->option('src',       ['array'   => true, 'default' => ['src']]);
            $commandLine->option('spec',      ['array'   => true, 'default' => ['spec']]);
            $commandLine->option('reporter',  ['array'   => true, 'default' => ['dot']]);
            $commandLine->option('pattern',   ['default' => ['*Spec.php', '*.spec.php']]);
            $commandLine->option('coverage',  ['type'    => 'string']);
            $commandLine->option('config',    ['default' => 'kahlan-config.php']);
            $commandLine->option('ff',        ['type'    => 'numeric', 'default' => 0]);
            $commandLine->option('cc',        ['type'    => 'boolean', 'default' => false]);
            $commandLine->option('no-colors', ['type'    => 'boolean', 'default' => false]);
            $commandLine->option('no-header', ['type'    => 'boolean', 'default' => false]);
            $commandLine->option('include',   [
                'array' => true,
                'default' => ['*'],
                'value' => function ($value) {
                    return array_filter($value);
                }
            ]);
            $commandLine->option('exclude',    [
                'array' => true,
                'default' => [],
                'value' => function ($value) {
                    return array_filter($value);
                }
            ]);
            $commandLine->option('persistent', ['type'  => 'boolean', 'default' => true]);
            $commandLine->option('autoclear',  ['array' => true, 'default' => [
                'Kahlan\Plugin\Monkey',
                'Kahlan\Plugin\Stub',
                'Kahlan\Plugin\Quit',
                'Kahlan\Plugin\Call\Calls'
            ]]);
        }

        /**
         * Get/set the attached autoloader instance.
         *
         * @return object
         */
        public function autoloader($autoloader = null)
        {
            if (!func_num_args()) {
                return $this->_autoloader;
            }
            $this->_autoloader = $autoloader;
            return $this;
        }

        /**
         * Returns arguments instance.
         *
         * @return object
         */
        public function commandLine()
        {
            return $this->_commandLine;
        }

        /**
         * Returns the suite instance.
         *
         * @return object
         */
        public function suite()
        {
            return $this->_suite;
        }

        /**
         * Returns the reporter container.
         *
         * @return object
         */
        public function reporters()
        {
            return $this->_reporters;
        }

        /**
         * Load the config.
         *
         * @param string $argv The command line string.
         */
        public function loadConfig($argv = [])
        {
            $commandLine = new CommandLine();
            $commandLine->option('config',  ['default'  => 'kahlan-config.php']);
            $commandLine->option('help',    ['type'     => 'boolean']);
            $commandLine->option('version', ['type'     => 'boolean']);
            $commandLine->parse($argv);

            $run = function ($commandLine) {
                if (file_exists($commandLine->get('config'))) {
                    require $commandLine->get('config');
                }
            };
            $run($commandLine);
            $this->_commandLine->parse($argv, false);

            if ($commandLine->get('help')) {
                return $this->_help();
            }

            if ($commandLine->get('version')) {
                return $this->_version();
            }
        }

        /**
         * Gets the default terminal console.
         *
         * @return object The default terminal console.
         */
        public function terminal()
        {
            return new Terminal([
                'colors' => !$this->commandLine()->get('no-colors'),
                'header' => !$this->commandLine()->get('no-header')
            ]);
        }

        /**
         * Echoes the Kahlan version.
         */
        protected function _version()
        {
            $terminal = $this->terminal();
            if (!$this->commandLine()->get('no-header')) {
                $terminal->write($terminal->kahlan() ."\n\n");
                $terminal->write($terminal->kahlanBaseline(), 'dark-grey');
                $terminal->write("\n\n");
            }

            $terminal->write("version ");
            $terminal->write(static::VERSION, 'green');
            $terminal->write("\n\n");
            $terminal->write("For additional help you must use ");
            $terminal->write("--help", 'green');
            $terminal->write("\n\n");
            QuitStatement::quit();
        }

        /**
         * Echoes the help message.
         */
        protected function _help()
        {
            $terminal = $this->terminal();
            if (!$this->commandLine()->get('no-header')) {
                $terminal->write($terminal->kahlan() ."\n\n");
                $terminal->write($terminal->kahlanBaseline(), 'dark-grey');
                $terminal->write("\n\n");
            }
            $help = <<<EOD

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
            $terminal->write($help);
            QuitStatement::quit();
        }

        /**
         * Regiter built-in matchers.
         */
        public static function registerMatchers()
        {
            Matcher::register('toBe',             'Kahlan\Matcher\ToBe');
            Matcher::register('toBeA',            'Kahlan\Matcher\ToBeA');
            Matcher::register('toBeAn',           'Kahlan\Matcher\ToBeA');
            Matcher::register('toBeAnInstanceOf', 'Kahlan\Matcher\ToBeAnInstanceOf');
            Matcher::register('toBeCloseTo',      'Kahlan\Matcher\ToBeCloseTo');
            Matcher::register('toBeEmpty',        'Kahlan\Matcher\ToBeFalsy');
            Matcher::register('toBeFalsy',        'Kahlan\Matcher\ToBeFalsy');
            Matcher::register('toBeGreaterThan',  'Kahlan\Matcher\ToBeGreaterThan');
            Matcher::register('toBeLessThan',     'Kahlan\Matcher\ToBeLessThan');
            Matcher::register('toBeNull',         'Kahlan\Matcher\ToBeNull');
            Matcher::register('toBeTruthy',       'Kahlan\Matcher\ToBeTruthy');
            Matcher::register('toContain',        'Kahlan\Matcher\ToContain');
            Matcher::register('toContainKey',     'Kahlan\Matcher\ToContainKey');
            Matcher::register('toContainKeys',    'Kahlan\Matcher\ToContainKey');
            Matcher::register('toEcho',           'Kahlan\Matcher\ToEcho');
            Matcher::register('toEqual',          'Kahlan\Matcher\ToEqual');
            Matcher::register('toHaveLength',     'Kahlan\Matcher\ToHaveLength');
            Matcher::register('toMatch',          'Kahlan\Matcher\ToMatch');
            Matcher::register('toReceive',        'Kahlan\Matcher\ToReceive');
            Matcher::register('toBeCalled',       'Kahlan\Matcher\ToBeCalled');
            Matcher::register('toThrow',          'Kahlan\Matcher\ToThrow');
            Matcher::register('toMatchEcho',      'Kahlan\Matcher\ToMatchEcho');
        }

        /**
         * Run the workflow.
         */
        public function run()
        {
            if (!defined('KAHLAN_FUNCTIONS_EXIST') && (!defined('KAHLAN_DISABLE_FUNCTIONS') || !KAHLAN_DISABLE_FUNCTIONS)) {
                fwrite(STDERR, "Kahlan's global functions are missing because of some naming collisions with another library.\n");
                exit(-1);
            }

            $this->_start = microtime(true);
            return Filter::on($this, 'workflow', [], function ($chain) {
                $this->_bootstrap();

                $this->_interceptor();

                $this->_namespaces();

                $this->_patchers();

                $this->_load();

                $this->_reporters();

                $this->_matchers();

                $this->_run();

                $this->_reporting();

                $this->_stop();

                $this->_quit();
            });
        }

        /**
         * Returns the exit status.
         *
         * @return integer The exit status.
         */
        public function status()
        {
            return $this->suite()->status();
        }

        /**
         * The default `'bootstrap'` filter.
         */
        protected function _bootstrap()
        {
            return Filter::on($this, 'bootstrap', [], function ($chain) {
                $this->suite()->backtraceFocus($this->commandLine()->get('pattern'));
                if (!$this->commandLine()->exists('coverage')) {
                    if ($this->commandLine()->exists('clover') || $this->commandLine()->exists('istanbul') || $this->commandLine()->exists('lcov')) {
                        $this->commandLine()->set('coverage', 1);
                    }
                }
            });
        }

        /**
         * The default `'interceptor'` filter.
         */
        protected function _interceptor()
        {
            return Filter::on($this, 'interceptor', [], function ($chain) {
                $this->autoloader(Interceptor::patch([
                    'loader'     => [$this->autoloader(), 'loadClass'],
                    'include'    => $this->commandLine()->get('include'),
                    'exclude'    => array_merge($this->commandLine()->get('exclude'), ['Kahlan\\']),
                    'persistent' => $this->commandLine()->get('persistent'),
                    'cachePath'  => rtrim(realpath(sys_get_temp_dir()), DS) . DS . 'kahlan',
                    'clearCache' => $this->commandLine()->get('cc')
                ]));
            });
        }

        /**
         * The default `'namespace'` filter.
         */
        protected function _namespaces()
        {
            return Filter::on($this, 'namespaces', [], function ($chain) {
                $paths = $this->commandLine()->get('spec');
                foreach ($paths as $path) {
                    $path = realpath($path);
                    $namespace = basename($path) . '\\';
                    $this->autoloader()->add($namespace, dirname($path));
                }
            });
        }

        /**
         * The default `'patcher'` filter.
         */
        protected function _patchers()
        {
            if (!$interceptor = Interceptor::instance()) {
                return;
            }
            return Filter::on($this, 'patchers', [], function ($chain) {
                $interceptor = Interceptor::instance();
                $patchers = $interceptor->patchers();
                $patchers->add('pointcut', new Pointcut());
                $patchers->add('monkey',   new Monkey());
                $patchers->add('rebase',   new Rebase());
                $patchers->add('quit',     new Quit());
            });
        }

        /**
         * The default `'load'` filter.
         */
        protected function _load()
        {
            return Filter::on($this, 'load', [], function ($chain) {
                $specDirs = $this->commandLine()->get('spec');
                foreach ($specDirs as $dir) {
                    if (!file_exists($dir)) {
                        fwrite(STDERR, "ERROR: unexisting `{$dir}` directory, use --spec option to set a valid one (ex: --spec=tests).\n");
                        exit(-1);
                    }
                }
                $files = Dir::scan($specDirs, [
                    'include' => $this->commandLine()->get('pattern'),
                    'exclude' => '*/.*',
                    'type' => 'file'
                ]);
                foreach ($files as $file) {
                    require $file;
                }
            });
        }

        /**
         * The default `'reporters'` filter.
         */
        protected function _reporters()
        {
            return Filter::on($this, 'reporters', [], function ($chain) {
                $this->_console();
                $this->_coverage();
            });
        }

        /**
         * The default `'console'` filter.
         */
        protected function _console()
        {
            return Filter::on($this, 'console', [], function ($chain) {
                $collection = $this->reporters();

                $reporters = $this->commandLine()->get('reporter');
                if (!$reporters) {
                    return;
                }

                foreach ($reporters as $reporter) {
                    $parts = explode(":", $reporter);
                    $name = $parts[0];
                    $output = isset($parts[1]) ? $parts[1] : null;

                    $args = $this->commandLine()->get('dot');
                    $args = $args ?: [];

                    if (!$name === null || $name === 'none') {
                        continue;
                    }

                    $params = $args + [
                        'start'  => $this->_start,
                        'colors' => !$this->commandLine()->get('no-colors'),
                        'header' => !$this->commandLine()->get('no-header'),
                        'src'    => $this->commandLine()->get('src'),
                        'spec'   => $this->commandLine()->get('spec'),
                    ];

                    if (isset($output) && strlen($output) > 0) {
                        if (file_exists($output) && !is_writable($output)) {
                            fwrite(STDERR, "Error: please check that file '{$output}' is writable\n");
                        } else {
                            $file = @fopen($output, 'w');
                            if (!$file) {
                                fwrite(STDERR, "Error: can't create file '{$output}' for write\n");
                            } else {
                                $params['output'] = $file;
                            }
                        }
                    }

                    $class = 'Kahlan\Reporter\\' . str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', trim($name))));
                    if (!class_exists($class)) {
                        fwrite(STDERR, "Error: unexisting reporter `'{$name}'` can't find class `$class`.\n");
                        exit(-1);
                    }
                    $collection->add($name, new $class($params));
                }
            });
        }

        /**
         * The default `'coverage'` filter.
         */
        protected function _coverage()
        {
            return Filter::on($this, 'coverage', [], function ($chain) {
                if (!$this->commandLine()->exists('coverage')) {
                    return;
                }
                $reporters = $this->reporters();
                $driver = null;

                if (PHP_SAPI === 'phpdbg') {
                    $driver = new Phpdbg();
                } elseif (extension_loaded('xdebug')) {
                    $driver = new Xdebug();
                } else {
                    fwrite(STDERR, "ERROR: PHPDBG SAPI has not been detected and Xdebug is not installed, code coverage can't be used.\n");
                    exit(-1);
                }
                $srcDirs = $this->commandLine()->get('src');
                foreach ($srcDirs as $dir) {
                    if (!file_exists($dir)) {
                        fwrite(STDERR, "ERROR: unexisting `{$dir}` directory, use --src option to set a valid one (ex: --src=app).\n");
                        exit(-1);
                    }
                }
                $coverage = new Coverage([
                    'verbosity' => $this->commandLine()->get('coverage') === null ? 1 : $this->commandLine()->get('coverage'),
                    'driver' => $driver,
                    'path' => $srcDirs,
                    'colors' => !$this->commandLine()->get('no-colors')
                ]);
                $reporters->add('coverage', $coverage);
            });
        }

        /**
         * The default `'matchers'` filter.
         */
        protected function _matchers()
        {
            return Filter::on($this, 'matchers', [], function ($chain) {
                static::registerMatchers();
            });
        }

        /**
         * The default `'run'` filter.
         */
        protected function _run()
        {
            return Filter::on($this, 'run', [], function ($chain) {
                $this->suite()->run([
                    'reporters' => $this->reporters(),
                    'autoclear' => $this->commandLine()->get('autoclear'),
                    'ff'        => $this->commandLine()->get('ff')
                ]);
            });
        }

        /**
         * The default `'reporting'` filter.
         */
        protected function _reporting()
        {
            return Filter::on($this, 'reporting', [], function ($chain) {
                $reporter = $this->reporters()->get('coverage');
                if (!$reporter) {
                    return;
                }
                if ($this->commandLine()->exists('clover')) {
                    Clover::write([
                        'collector' => $reporter,
                        'file' => $this->commandLine()->get('clover')
                    ]);
                }
                if ($this->commandLine()->exists('istanbul')) {
                    Istanbul::write([
                        'collector' => $reporter,
                        'file' => $this->commandLine()->get('istanbul')
                    ]);
                }
                if ($this->commandLine()->exists('lcov')) {
                    Lcov::write([
                        'collector' => $reporter,
                        'file' => $this->commandLine()->get('lcov')
                    ]);
                }
            });
        }

        /**
         * The default `'stop'` filter.
         */
        protected function _stop()
        {
            return Filter::on($this, 'stop', [], function ($chain) {
                $this->suite()->stop();
            });
        }


        /**
         * The default `'quit'` filter.
         */
        protected function _quit()
        {
            return Filter::on($this, 'quit', [$this->suite()->passed()], function ($chain, $success) {
            });
        }
    }

    define('KAHLAN_VERSION', Kahlan::VERSION);

}

namespace {

    use Kahlan\Expectation;
    use Kahlan\Suite;
    use Kahlan\Specification;
    use Kahlan\Allow;
    use Kahlan\Box\BoxException;
    use Kahlan\Box\Box;

    /**
     * Create global functions
     */
    function initKahlanGlobalFunctions()
    {
        if (getenv('KAHLAN_DISABLE_FUNCTIONS') || (defined('KAHLAN_DISABLE_FUNCTIONS') && KAHLAN_DISABLE_FUNCTIONS)) {
            return;
        }

        if (defined('KAHLAN_FUNCTIONS_EXIST') && KAHLAN_FUNCTIONS_EXIST) {
            return;
        }

        $error = false;

        $exit = function ($name) use (&$error) {
            fwrite(STDERR, "The Kahlan global function `{$name}()`s can't be created because of some naming collisions with another library.\n");
            $error = true;
        };

        define('KAHLAN_FUNCTIONS_EXIST', true);

        if (!function_exists('beforeAll')) {
            function beforeAll($closure)
            {
                return Suite::current()->beforeAll($closure);
            }
        } else {
            $exit('beforeAll');
        }

        if (!function_exists('afterAll')) {
            function afterAll($closure)
            {
                return Suite::current()->afterAll($closure);
            }
        } else {
            $exit('afterAll');
        }

        if (!function_exists('beforeEach')) {
            function beforeEach($closure)
            {
                return Suite::current()->beforeEach($closure);
            }
        } else {
            $exit('beforeEach');
        }

        if (!function_exists('afterEach')) {
            function afterEach($closure)
            {
                return Suite::current()->afterEach($closure);
            }
        } else {
            $exit('afterEach');
        }

        if (!function_exists('describe')) {
            function describe($message, $closure, $timeout = null, $type = 'normal')
            {
                if (!Suite::current()) {
                    $suite = \Kahlan\box('kahlan')->get('suite.global');
                    return $suite->describe($message, $closure, $timeout, $type);
                }
                return Suite::current()->describe($message, $closure, $timeout, $type);
            }
        } else {
            $exit('describe');
        }

        if (!function_exists('context')) {
            function context($message, $closure, $timeout = null, $type = 'normal')
            {
                return Suite::current()->context($message, $closure, $timeout, $type);
            }
        } else {
            $exit('context');
        }

        if (!function_exists('given')) {
            function given($name, $value)
            {
                return Suite::current()->given($name, $value);
            }
        } else {
            $exit('given');
        }

        if (!function_exists('it')) {
            function it($message, $closure = null, $timeout = null, $type = 'normal')
            {
                return Suite::current()->it($message, $closure, $timeout, $type);
            }
        } else {
            $exit('it');
        }

        if (!function_exists('fdescribe')) {
            function fdescribe($message, $closure, $timeout = null)
            {
                return describe($message, $closure, $timeout, 'focus');
            }
        } else {
            $exit('fdescribe');
        }

        if (!function_exists('fcontext')) {
            function fcontext($message, $closure, $timeout = null)
            {
                return context($message, $closure, $timeout, 'focus');
            }
        } else {
            $exit('fcontext');
        }

        if (!function_exists('fit')) {
            function fit($message, $closure = null, $timeout = null)
            {
                return it($message, $closure, $timeout, 'focus');
            }
        } else {
            $exit('fit');
        }

        if (!function_exists('xdescribe')) {
            function xdescribe($message, $closure, $timeout = null)
            {
                return describe($message, $closure, $timeout, 'exclude');
            }
        } else {
            $exit('xdescribe');
        }

        if (!function_exists('xcontext')) {
            function xcontext($message, $closure, $timeout = null)
            {
                return context($message, $closure, $timeout, 'exclude');
            }
        } else {
            $exit('xcontext');
        }

        if (!function_exists('xit')) {
            function xit($message, $closure = null, $timeout = null)
            {
                return it($message, $closure, $timeout, 'exclude');
            }
        } else {
            $exit('xit');
        }

        if (!function_exists('waitsFor')) {
            function waitsFor($actual, $timeout = null)
            {
                return Specification::current()->waitsFor($actual, $timeout);
            }
        } else {
            $exit('waitsFor');
        }

        if (!function_exists('skipIf')) {
            function skipIf($condition)
            {
                $current = Specification::current() ?: Suite::current();
                return $current->skipIf($condition);
            }
        } else {
            $exit('skipIf');
        }

        if (!function_exists('expect')) {
            /**
             * @param $actual
             *
             * @return Expectation
             */
            function expect($actual)
            {
                return Specification::current()->expect($actual);
            }
        } else {
            $exit('expect');
        }

        if (!function_exists('allow')) {
            /**
             * @param $actual
             *
             * @return Allow
             */
            function allow($actual)
            {
                return new Allow($actual);
            }
        } else {
            $exit('allow');
        }

        if ($error) {
            exit(-1);
        }
    }
}
