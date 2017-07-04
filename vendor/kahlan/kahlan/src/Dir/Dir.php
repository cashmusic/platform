<?php
namespace Kahlan\Dir;

use Exception;
use DirectoryIterator;
use SplFileObject;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class Dir extends \FilterIterator
{
    /**
     * List of include patterns
     *
     * @var array
     */
    protected $_include = [];

    /**
     * List of exclude patterns
     *
     * @var array
     */
    protected $_exclude = [];

    /**
     * List of allowed types
     *
     * @var array
     */
    protected $_types = [];

    /**
     * Scans one or many directories for files.
     *
     * @param  array|string $path    Path or paths to scan.
     * @param  array        $options Scanning options. Possible values are:
     *                               -`'iterator'`       _integer_     : The iterator mode.
     *                               -`'skipDots'`       _boolean_     : Keeps '.' and '..' if `true`.
     *                               -`'leavesOnly'`     _boolean_     : Keeps only leaves if `true`.
     *                               -`'followSymlinks'` _boolean_     : Follows Symlinks if `true`.
     *                               -`'recursive'`      _boolean_     : Scans recursively if `true`.
     *                               -`'include'`        _string|array_: An array of includes.
     *                               -`'exclude'`        _string|array_: An array of excludes.
     *                               -`'type'`           _string|array_: An array of types.
     * @return array
     * @throws Exception
     */
    public static function scan($path, $options = [])
    {
        $defaults = [
            'iterator'       => RecursiveIteratorIterator::SELF_FIRST,
            'skipDots'       => true,
            'leavesOnly'     => false,
            'followSymlinks' => true,
            'recursive'      => true
        ];
        $options += $defaults;

        $paths = (array) $path;

        $dirFlags = static::_dirFlags($options);
        $iteratorFlags = static::_iteratorFlags($options);

        $result = [];
        foreach ($paths as $path) {
            $result = array_merge($result, static::_scan($path, $options, $dirFlags, $iteratorFlags));
        }
        return $result;
    }

    /**
     * Scans a given directory for files.
     *
     * @param  string    $path    Path or paths to scan.
     * @param  array     $options Scanning options. Possible values are:
     *                            -`'iterator'`       _integer_     : The iterator mode.
     *                            -`'skipDots'`       _boolean_     : Keeps '.' and '..' if `true`.
     *                            -`'leavesOnly'`     _boolean_     : Keeps only leaves if `true`.
     *                            -`'followSymlinks'` _boolean_     : Follows Symlinks if `true`.
     *                            -`'recursive'`      _boolean_     : Scans recursively if `true`.
     *                            -`'include'`        _string|array_: An array of includes.
     *                            -`'exclude'`        _string|array_: An array of excludes.
     *                            -`'type'`           _string|array_: An array of types.
     * @return array
     */
    protected static function _scan($path, $options, $dirFlags, $iteratorFlags)
    {
        if (!file_exists($path)) {
            throw new Exception("Unexisting path `{$path}`.");
        }
        if (!is_dir($path)) {
            return [$path];
        }
        $worker = new RecursiveDirectoryIterator($path, $dirFlags);
        if ($options['recursive']) {
            $worker = new RecursiveIteratorIterator($worker, $iteratorFlags);
        }
        $filter = new static($worker);
        $filter->filter($options);

        $result = [];
        foreach ($filter as $key => $value) {
            $result[] = $key;
        }
        return $result;
    }

    /**
     * Returns `RecursiveIteratorIterator` flags from `Dir` options.
     *
     * @param  array   $options Scanning options. Possible values are:
     *                          -`'iterator'`   _integer_ : The iterator mode.
     *                          -`'leavesOnly'` _boolean_ : Keeps only leaves if `true`.
     * @return integer          Some `RecursiveIteratorIterator` flags
     */
    public static function _iteratorFlags($options)
    {
        $flag = $options['leavesOnly'] ? RecursiveIteratorIterator::LEAVES_ONLY : $options['iterator'];
        return $flag;
    }

    /**
     * Returns `FilesystemIterator` flags from `Dir` options.
     *
     * @param  array   $options Scanning options. Possible values are:
     *                          -`'skipDots'`       _boolean_ : Keeps '.' and '..' if `true`.
     *                          -`'followSymlinks'` _boolean_ : Follows Symlinks if `true`.
     * @return integer          Some `FilesystemIterator` flags
     */
    public static function _dirFlags($options)
    {
        $flag = $options['followSymlinks'] ? FilesystemIterator::FOLLOW_SYMLINKS : 0;
        $flag |= $options['skipDots'] ? FilesystemIterator::SKIP_DOTS : 0;
        $flag |= FilesystemIterator::UNIX_PATHS;
        return $flag;
    }

    /**
     * Removes one or many directories.
     *
     * @param  array|string  $path    Path or paths to scan.
     * @param  array         $options Scanning options. Possible values are:
     *                                -`'followSymlinks'` _boolean_     : Follows Symlinks if `true`.
     *                                -`'recursive'`      _boolean_     : Scans recursively if `true`.
     */
    public static function remove($path, $options = [])
    {
        $defaults = [
            'followSymlinks' => false,
            'recursive'      => true
        ];
        $options += $defaults;

        $options['type'] = [];
        $options['skipDots'] = true;
        $options['leavesOnly'] = false;
        $options['iterator'] = RecursiveIteratorIterator::CHILD_FIRST;
        unset($options['include']);
        unset($options['exclude']);

        $paths = array_merge(static::scan($path, $options), (array) $path);

        foreach ($paths as $path) {
            is_dir($path) ? rmdir($path) : unlink($path);
        }
    }

    /**
     * Copies a directory.
     *
     * @param  array|string  $path    Source directory.
     * @param  string        $dest    Destination directory.
     * @param  array         $options Scanning options. Possible values are:
     *                                -`'mode'`           _integer_     : Mode used for directory creation.
     *                                -`'childrenOnly'`     _boolean_     : Excludes parent directory if `true`.
     *                                -`'followSymlinks'` _boolean_     : Follows Symlinks if `true`.
     *                                -`'recursive'`      _boolean_     : Scans recursively if `true`.
     * @return array
     * @throws Exception
     */
    public static function copy($path, $dest, $options = [])
    {
        $defaults = [
            'mode'           => 0755,
            'childrenOnly'     => false,
            'followSymlinks' => true,
            'recursive'      => true
        ];
        $options += $defaults;

        $options['type'] = [];
        $options['skipDots'] = true;
        $options['leavesOnly'] = false;
        $options['iterator'] = RecursiveIteratorIterator::SELF_FIRST;
        unset($options['include']);
        unset($options['exclude']);

        $sources = (array) $path;

        if (!is_dir($dest)) {
            throw new Exception("Unexisting destination path `{$dest}`.");
        }

        foreach ($sources as $path) {
            static::_copy($path, $dest, $options);
        }
    }

    /**
     * Copies a directory.
     *
     * @param  string    $path Source directory.
     * @param  string    $dest Destination directory.
     * @param  array     $options Scanning options. Possible values are:
     *                            -`'mode'`           _integer_     : Mode used for directory creation.
     *                            -`'childrenOnly'`     _boolean_     : Excludes parent directory if `true`.
     *                            -`'followSymlinks'` _boolean_     : Follows Symlinks if `true`.
     *                            -`'recursive'`      _boolean_     : Scans recursively if `true`.
     *                            -`'include'`        _string|array_: An array of includes.
     *                            -`'exclude'`        _string|array_: An array of excludes.
     * @return array
     * @throws Exception
     */
    protected static function _copy($path, $dest, $options)
    {
        $ds = DIRECTORY_SEPARATOR;
        $root = $options['childrenOnly'] ? $path : dirname($path);
        $dest = rtrim($dest, $ds);

        $paths = static::scan($path, $options);

        foreach ($paths as $path) {
            $target = preg_replace('~^' . preg_quote(rtrim($root, $ds)) . '~', '', $path);
            $isDir = is_dir($path);
            $dirname = $dest . $ds . ltrim($isDir ? $target : dirname($target), $ds);
            if (!file_exists($dirname)) {
                mkdir($dirname, $options['mode'], true);
            }
            if (!$isDir) {
                copy($path, $dest . $ds . ltrim($target, $ds));
            }
        }
    }

    /**
     * Creates a directory.
     *
     * @param  array|string $path    The directory path.
     * @param  array        $options Possible options values are:
     *                               -`'mode'`      _integer_ : Mode used for directory creation.
     *                               -`'recursive'` _boolean_ : Scans recursively if `true`.
     * @return boolean
     */
    public static function make($path, $options = [])
    {
        $defaults = [
            'mode'           => 0755,
            'recursive'      => true
        ];
        $options += $defaults;

        if (!is_array($path)) {
            return mkdir($path, $options['mode'], $options['recursive']);
        }

        $result = [];
        foreach ($path as $p) {
            $result[] = static::make($p, $options);
        }
        return !!array_filter($result);
    }

    /**
     * Creates a directory with unique file name.
     *
     * @see http://php.net/manual/en/function.tempnam.php
     *
     * @param  string $path   The directory where the temporary filename will be created.
     * @param  string $prefix The prefix of the generated temporary filename.
     * @return string
     */
    public static function tempnam($path = null, $prefix = '')
    {
        if ($path === null) {
            $path = sys_get_temp_dir();
        }

        if ($tempfile = tempnam($path, $prefix)) {
            unlink($tempfile);
            mkdir($tempfile);
        }

        return $tempfile;
    }

    /**
     * Applies some filters to a `FilterIterator` instance.
     *
     * @param  array     $options The filters optoins. Possible values are:
     *                            -`'include'` _string|array_: An array of includes.
     *                            -`'exclude'` _string|array_: An array of excludes.
     *                            -`'type'`    _string|array_: An array of types.
     */
    public function filter($options = [])
    {
        $defaults = array(
            'include' => ['*'],
            'exclude' => [],
            'type' => []
        );
        $options += $defaults;
        $this->_exclude = (array) $options['exclude'];
        $this->_include = (array) $options['include'];
        $this->_types = (array) $options['type'];
    }

    /**
     * Checks if a file passes the setted filters.
     *
     * @return boolean
     */
    public function accept()
    {
        $path = $this->current()->getPathname();
        if ($this->_excluded($path)) {
            return false;
        }
        if (!$this->_included($path)) {
            return false;
        }
        return $this->_matchType();
    }

    /**
     * Checks if a file passes match an excluded path.
     *
     * @return boolean Returns `true` if match an excluded path, `false` otherwise.
     */
    protected function _excluded($path)
    {
        foreach ($this->_exclude as $exclude) {
            if (fnmatch($exclude, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a file passes match an included path.
     *
     * @return boolean Returns `true` if match an included path, `false` otherwise.
     */
    protected function _included($path)
    {
        foreach ($this->_include as $include) {
            if (fnmatch($include, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a file passes match the allowed type.
     *
     * @return boolean Returns `true` if match the allowed type, `false` otherwise.
     */
    protected function _matchType()
    {
        if (!$this->_types) {
            return true;
        }
        $file = $this->current();
        foreach ($this->_types as $type) {
            $method = 'is' . ucfirst($type);
            if ($file->$method()) {
                return true;
            }
        }
        return false;
    }
}
