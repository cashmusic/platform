<?php
namespace Kahlan\Spec\Proxy;

use Kahlan\Plugin\Pointcut;

class Autoloader extends \Composer\Autoload\ClassLoader
{
    public function __construct($composer)
    {
        //Hack to early load somes classes.
        class_exists('Kahlan\Plugin\Pointcut');
        class_exists('Kahlan\Plugin\Stub');
        //class_exists('Kahlan\Plugin\Call');
        $this->_composer = $composer;
    }

    public function loadClass($class)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->loadClass($class);
    }

    public function findFile($class)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->findFile($class);
    }

    public function add($prefix, $paths, $prepend = false)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->add($prefix, $paths, $prepend);
    }

    public function addPsr4($prefix, $paths, $prepend = false)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->addPsr4($prefix, $paths, $prepend);
    }

    public function addClassMap(array $classMap)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->addClassMap($classMap);
    }

    public function set($prefix, $paths)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->set($prefix, $paths);
    }

    public function setPsr4($prefix, $paths)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->setPsr4($prefix, $paths);
    }

    public function getClassMap()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->getClassMap();
    }

    public function getPrefixes()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->getPrefixes();
    }

    public function getPrefixesPsr4()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->getPrefixesPsr4();
    }

    public function setUseIncludePath($useIncludePath)
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->setUseIncludePath($useIncludePath);
    }

    public function getUseIncludePath()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->getUseIncludePath();
    }

    public function getFallbackDirs()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->getFallbackDirs();
    }

    public function getFallbackDirsPsr4()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return $this->_composer->getFallbackDirsPsr4();
    }

    public function __call($method, $args)
    {
        $self = isset($this) ? $this : get_called_class();
        $class = isset($this) ? get_class($this) : get_called_class();
        if ($pointcut = Pointcut::before("{$class}::{$method}", $self, $args)) {
            return $pointcut($self, $args);
        }
    }

    public function getPrefixesCustom()
    {
        $args = func_get_args();
        $self = isset($this) ? $this : get_called_class();
        if ($pointcut = Pointcut::before(__METHOD__, $self, $args)) {
            return $pointcut($self, $args);
        }

        return [];
    }
}
