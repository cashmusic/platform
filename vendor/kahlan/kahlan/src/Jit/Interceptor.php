<?php
namespace Kahlan\Jit;

use Exception;
use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Composer\Autoload\ClassLoader;

class Interceptor
{

    /**
     * Cache path. If `false` the caching is not enable.
     *
     * @var string
     */
    protected $_cachePath = false;

    /**
     * Additionnal watched files.
     *
     * @var integer
     */
    protected $_watched = [];

    /**
     * Most recent modification timestamps of the watched files.
     *
     * @var integer
     */
    protected $_watchedTimestamp = 0;

    /**
     * Method name for loading a class.
     *
     * @var string
     */
    protected $_loadClass = 'loadClass';

    /**
     * Method name for finding files on original autoloader.
     *
     * @var string
     */
    protected $_findFile = 'findFile';

    /**
     * Method name for adding a set of PSR-0 directories.
     *
     * @var string
     */
    protected $_add = 'add';

    /**
     * Method name for adding a set of PSR-4 directories.
     *
     * @var string
     */
    protected $_addPsr4 = 'addPsr4';

    /**
     * Method name for adding a set of PSR-0 directories.
     *
     * @var string
     */
    protected $_getPrefixes = 'getPrefixes';

    /**
     * Method name for adding a set of PSR-4 directories.
     *
     * @var string
     */
    protected $_getPrefixesPsr4 = 'getPrefixesPsr4';

    /**
     * The patchers container.
     *
     * @var object
     */
    protected $_patchers = null;

    /**
     * Allowed namespaces/classes for being patched (if empty, mean all is allowed).
     *
     * @var array
     */
    protected $_include = [];

    /**
     * Namespaces/classes which must not be patched.
     *
     * @var array
     */
    protected $_exclude = [];

    /**
     * The patched loader reference.
     *
     * @var array
     */
    protected $_originalLoader = null;

    /**
     * Overrided loader reference.
     *
     * @var array
     */
    protected static $_interceptor = null;

    /**
     * Constructs
     *
     * @param array $options Options for the constructor.
     */
    public function __construct($options = [])
    {
        $defaults = [
            'originalLoader'  => null,
            'patchers'        => null,
            'exclude'         => [],
            'include'         => ['*'],
            'loadClass'       => 'loadClass',
            'findFile'        => 'findFile',
            'add'             => 'add',
            'addPsr4'         => 'addPsr4',
            'getPrefixes'     => 'getPrefixes',
            'getPrefixesPsr4' => 'getPrefixesPsr4',
            'watch'           => [],
            'cachePath'       => rtrim(sys_get_temp_dir(), DS) . DS . 'jit',
            'clearCache'      => false
        ];
        $options += $defaults;
        $this->_originalLoader = $options['originalLoader'];
        $this->_patchers = new Patchers();
        $this->_loadClass = $options['loadClass'];
        $this->_findFile = $options['findFile'];
        $this->_add = $options['add'];
        $this->_addPsr4 = $options['addPsr4'];
        $this->_getPrefixes = $options['getPrefixes'];
        $this->_getPrefixesPsr4 = $options['getPrefixesPsr4'];
        $this->_cachePath = rtrim($options['cachePath'], DS);
        $this->_exclude = (array) $options['exclude'];
        $this->_exclude[] = 'jit\\';
        $this->_include = (array) $options['include'];

        if ($options['clearCache']) {
            $this->clearCache();
        }

        if ($options['watch']) {
            $this->watch($options['watch']);
        }
    }

    /**
     * Patch the autoloader to be intercepted by the current autoloader.
     *
     * @param  array            $options Options for the interceptor autoloader.
     * @throws JitException
     */
    public static function patch($options = [])
    {
        if (static::$_interceptor) {
            throw new JitException("An interceptor is already attached.");
        }
        $defaults = ['loader' => null];
        $options += $defaults;
        $loader = $options['originalLoader'] = $options['loader'] ?: static::composer();
        if (!$loader) {
            throw new JitException("The loader option need to be a valid autoloader.");
        }
        unset($options['loader']);

        class_exists('Kahlan\Jit\JitException');
        class_exists('Kahlan\Jit\Node\NodeDef');
        class_exists('Kahlan\Jit\Node\FunctionDef');
        class_exists('Kahlan\Jit\Node\BlockDef');
        class_exists('Kahlan\Jit\TokenStream');
        class_exists('Kahlan\Jit\Parser');

        $interceptor = new static($options);
        spl_autoload_unregister($loader);
        return static::load($interceptor) ? $interceptor : false;
    }

    /**
     * Look for the composer autoloader.
     *
     * @param  array $options Options for the interceptor autoloader.
     * @return mixed          The founded composer autolaoder or `null` if not found.
     */
    public static function composer()
    {
        $loaders = spl_autoload_functions();

        foreach ($loaders as $key => $loader) {
            if (is_array($loader) && ($loader[0] instanceof ClassLoader)) {
                return $loader;
            }
        }
    }

    /**
     * Returns the interceptor autoloader instance.
     *
     * @return object|null
     */
    public static function instance()
    {
        return static::$_interceptor;
    }

    /**
     * Loads an interceptor autoloader.
     *
     * @param  array   $loader The autoloader to use.
     * @return boolean         Returns `true` on success, `false` otherwise.
     */
    public static function load($interceptor = null)
    {
        if (static::$_interceptor) {
            static::unpatch();
        }

        $original = $interceptor->originalLoader();
        $success = spl_autoload_register($interceptor->loader(), true, true);
        spl_autoload_unregister($original);

        static::$_interceptor = $interceptor;
        return $success;
    }

    /**
     * Restore the original autoloader behavior.
     */
    public static function unpatch()
    {
        if (!static::$_interceptor) {
            return false;
        }

        $interceptor = static::$_interceptor;
        $original = $interceptor->originalLoader();

        spl_autoload_register($original, true, true);
        $success = spl_autoload_unregister($interceptor->loader());

        static::$_interceptor = null;
        return $success;
    }

    /**
     * Sets some file to watch.
     *
     * When a watched file is modified, any cached file are invalidated.
     *
     * @param $files The array of file paths to watch.
     */
    public function watch($files)
    {
        $files = (array) $files;

        foreach ($files as $file) {
            $path = realpath($file);
            $this->_watched[$path] = $path;
        }
        $this->refreshWatched();
    }

    /**
     * Unwatch a watched file
     *
     * @param $files The array of file paths to unwatch.
     */
    public function unwatch($files)
    {
        $files = (array) $files;

        foreach ($files as $file) {
            $path = realpath($file);
            unset($this->_watched[$path]);
        }
        $this->refreshWatched();
    }

    /**
     * Returns watched files
     *
     * @return The array of wateched file paths.
     */
    public function watched()
    {
        return array_values($this->_watched);
    }

    /**
     * Refresh watched file timestamps
     */
    public function refreshWatched()
    {
        $timestamps = [0];
        foreach ($this->_watched as $path) {
            $timestamps[] = filemtime($path);
        }
        $this->_watchedTimestamp = max($timestamps);
    }

    /**
     * Returns the interceptor autoload function.
     *
     * @return array
     */
    public function loader()
    {
        return [$this, $this->_loadClass];
    }

    /**
     * Returns the patched autoload function.
     *
     * @return array
     */
    public function originalLoader()
    {
        return $this->_originalLoader;
    }

    /**
     * Returns the patched autoloader instance.
     *
     * @return array
     */
    public function originalInstance()
    {
        return $this->_originalLoader[0];
    }

    /**
     * Returns the patchers container.
     *
     * @return mixed
     */
    public function patchers()
    {
        return $this->_patchers;
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string       $class The name of the class.
     * @return boolean|null        Returns `true` if loaded, `null` otherwise.
     */
    public function loadClass($class)
    {
        if (!$file = $this->findFile($class)) {
            return;
        }
        if (!$this->patchable($class)) {
            include $file;
            return true;
        }
        return $this->loadFile($file);
    }

    /**
     * Checks if a class can be patched or not.
     *
     * @param  string  $class The name of the class to check.
     * @return boolean        Returns `true` if the class need to be patched, `false` otherwise.
     */
    public function patchable($class)
    {
        if (!$this->allowed($class)) {
            return false;
        }
        return $this->patchers()->patchable($class);
    }

    /**
     * Checks if a class is allowed to be patched.
     *
     * @param  string  $class The name of the class to check.
     * @return boolean        Returns `true` if the class is allowed to be patched, `false` otherwise.
     */
    public function allowed($class)
    {
        foreach ($this->_exclude as $namespace) {
            if (strpos($class, $namespace) === 0) {
                return false;
            }
        }
        if ($this->_include === ['*']) {
            return true;
        }
        foreach ($this->_include as $namespace) {
            if (strpos($class, $namespace) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Loads a file.
     *
     * @param  string       $file  The path of the file.
     * @return boolean             Returns `true` if loaded, null otherwise.
     * @throws JitException
     */
    public function loadFile($filepath)
    {
        $file = realpath($filepath);
        if ($file === false) {
            throw new JitException("Error, the file `'{$filepath}'` doesn't exist.");
        }
        if ($cached = $this->cached($file)) {
            require $cached;
            return true;
        }
        $code = file_get_contents($file);
        $timestamp = filemtime($file);

        $rewrited = $this->_patchers->process($code, $file);
        $cached = $this->cache($file, $rewrited, max($timestamp, $this->_watchedTimestamp) + 1);
        require $cached;
        return true;
    }

    /**
     * Manualy load files.
     *
     * @param array An array of files to load.
     */
    public function loadFiles($files)
    {
        $files = (array) $files;

        $success = true;
        foreach ($files as $file) {
            $this->loadFile($file);
        }
        return true;
    }

    /**
     * Cache helper.
     *
     * @param  string $file    The source file path.
     * @param  string $content The patched content to cache.
     * @return string          The patched file path or the cache path if called with no params.
     */
    public function cache($file, $content, $timestamp = null)
    {
        if (!$cachePath = $this->cachePath()) {
            throw new JitException('Error, any cache path has been defined.');
        }
        $path = $cachePath . DS . ltrim(preg_replace('~:~', '', $file), DS);

        if (!@file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        if (file_put_contents($path, $content) === false) {
            throw new JitException("Unable to create a cached file at `'{$file}'`.");
        }
        if ($timestamp) {
            touch($path, $timestamp);
        }
        return $path;
    }

    /**
     * Gets a cached file path.
     *
     * @param  string         $file The source file path.
     * @return string|boolean       The cached file path or `false` if the cached file is not valid
     *                              or is not cached.
     */
    public function cached($file)
    {
        if (!$cachePath = $this->cachePath()) {
            return false;
        }
        $path = $cachePath . DS . ltrim(preg_replace('~:~', '', $file), DS);

        if (!@file_exists($path)) {
            return false;
        }

        $timestamp = filemtime($path);
        if ($timestamp > filemtime($file) && $timestamp > $this->_watchedTimestamp) {
            return $path;
        }
        return false;
    }

    /**
     * Returns the cache path.
     *
     * @return string
     */
    public function cachePath()
    {
        return rtrim($this->_cachePath);
    }

    /**
     * Clear the cache.
     */
    public function clearCache()
    {
        $cachePath = $this->cachePath();

        if (!file_exists($cachePath)) {
            return;
        }

        $dir = new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            $path = $file->getRealPath();
            $file->isDir() ? rmdir($path) : unlink($path);
        }
        rmdir($cachePath);
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param  string       $class The name of the class
     * @return string|false        The path if found, false otherwise
     */
    public function findFile($class)
    {
        $findFile = $this->_findFile;
        $file = static::originalInstance()->{$findFile}($class);
        if ($file !== false) {
            $file = realpath($file);
        }
        return $this->_patchers->findFile($this, $class, $file);
    }

    /**
     * Delegates call to original autoloader
     *
     * @param  $method The method name.
     * @param  $params The parameters
     * @return mixed
     */
    public function __call($method, $params)
    {
        $original = static::originalInstance();
        $attribute = "_{$method}";
        if (isset($this->$attribute)) {
            $method = $this->$attribute;
        }
        return call_user_func_array([$original, $method], $params);
    }

    /**
     * Returns both PSR-0 & PSR-4 prefixes and related paths.
     *
     * @return array
     */
    public function getPrefixes()
    {
        $ds = DIRECTORY_SEPARATOR;
        $getPrefixes = $this->_getPrefixes;
        $getPrefixesPsr4 = $this->_getPrefixesPsr4;
        $original = static::originalInstance();
        $paths = $getPrefixesPsr4 ? $original->{$getPrefixesPsr4}() : [];
        foreach ($original->{$getPrefixes}() as $namespace => $dirs) {
            foreach ($dirs as $key => $dir) {
                $paths[$namespace][$key] = $dir . $ds . trim(strtr($namespace, '\\', $ds), $ds);
            }
        }
        return $paths;
    }

    /**
     * Returns the path of a namespace or fully namespaced class name.
     *
     * @param  string      $namespace A namespace.
     * @param  boolean     $forceDir  Only consider directories paths.
     * @return string|null            Returns the found path or `null` if not path is found.
     */
    public function findPath($namespace, $forceDir = false)
    {
        $loader = static::originalInstance();

        $paths = static::getPrefixes();
        $logicalPath = trim(strtr($namespace, '\\', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);

        foreach ($paths as $prefix => $dirs) {
            if (strpos($namespace, $prefix) !== 0) {
                continue;
            }
            foreach ($dirs as $dir) {
                $root = $dir . DIRECTORY_SEPARATOR . substr($logicalPath, strlen($prefix));

                if ($path = $this->_path($root, $forceDir)) {
                    return realpath($path);
                }
            }
        }
    }

    /**
     * Build full path according to a root path.
     *
     * @param  string      $path      A root path.
     * @param  boolean     $forceDir  Only consider directories paths.
     * @return string|null            Returns the found path or `null` if not path is found.
     */
    protected function _path($path, $forceDir)
    {
        if ($forceDir) {
            return is_dir($path) ? $path : null;
        }
        if (file_exists($file = $path . '.php')) {
            return $file ;
        }
        if (file_exists($file = $path . '.hh')) {
            return $file;
        }
        if (is_dir($path)) {
            return $path;
        }
    }
}
