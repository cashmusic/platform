<?php
namespace Kahlan\Spec\Jit\Suite;

use Kahlan\Dir\Dir;
use Kahlan\Jit\JitException;
use Kahlan\Jit\Patchers;
use Kahlan\Jit\Interceptor;

use Kahlan\Spec\Proxy\Autoloader;
use Kahlan\Spec\Mock\Patcher;

describe("Interceptor", function () {

    beforeAll(function () {
        $this->previous = Interceptor::instance();
        Interceptor::unpatch();

        $this->composer = Interceptor::composer();
        skipIf(!$this->composer);

        $composer = clone $this->composer[0];
        $this->autoloader = new Autoloader($composer);
        spl_autoload_register([$this->autoloader, 'loadClass']);
        spl_autoload_unregister($this->composer);

        $this->cachePath = Dir::tempnam(null, 'cache');
    });

    afterEach(function () {
        Interceptor::unpatch();
    });

    afterAll(function () {
        spl_autoload_register($this->composer);
        spl_autoload_unregister([$this->autoloader, 'loadClass']);
        Dir::remove($this->cachePath);
        Interceptor::load($this->previous);
    });

    describe("::patch()", function () {

        it("patches the composer autoloader by default", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);
            expect($interceptor->originalInstance())->toBeAnInstanceOf("Composer\Autoload\ClassLoader");

        });

        it("throws an exception if the autoloader has already been patched", function () {

            $closure = function () {
                Interceptor::patch(['cachePath' => $this->cachePath]);
                Interceptor::patch(['cachePath' => $this->cachePath]);
            };
            expect($closure)->toThrow(new JitException("An interceptor is already attached."));

        });

        it("throws an exception if the autoloader has already been patched", function () {

            spl_autoload_unregister([$this->autoloader, 'loadClass']);

            $message = '';
            try {
                Interceptor::patch(['cachePath' => $this->cachePath]);
            } catch (JitException $e) {
                $message = $e->getMessage();
            }

            spl_autoload_register([$this->autoloader, 'loadClass']);

            expect($message)->toBe("The loader option need to be a valid autoloader.");
        });

        it("allows to configure the autoloader method", function () {

            $interceptor = Interceptor::patch([
                'cachePath' => $this->cachePath,
                'loadClass' => 'loadClassCustom',
            ]);

            expect($interceptor->loader()[1])->toBe('loadClassCustom');

        });

        it("throws an exception if the autoloader has already been patched", function () {

            $interceptor = Interceptor::patch([
                'cachePath'       => $this->cachePath,
                //'findFile'        => 'findFileCustom',
                'add'             => 'addCustom',
                'addPsr4'         => 'addPsr4Custom',
                'getPrefixes'     => 'getPrefixesCustom',
                'getPrefixesPsr4' => 'getPrefixesPsr4Custom',
            ]);

            expect($this->autoloader)->toReceive('addCustom')->with('namespace', 'file/path');
            expect($this->autoloader)->toReceive('addPsr4Custom')->with('namespace', 'file/path');
            expect($this->autoloader)->toReceive('getPrefixesCustom');
            expect($this->autoloader)->toReceive('getPrefixesPsr4Custom');

            $interceptor->add('namespace', 'file/path');
            $interceptor->addPsr4('namespace', 'file/path');
            $interceptor->getPrefixes();
            $interceptor->getPrefixesPsr4();
        });

    });

    describe("::unpatch()", function () {

        it("detaches the patched autoloader", function () {

            Interceptor::patch(['cachePath' => $this->cachePath]);

            $success = Interceptor::unpatch();
            $actual = Interceptor::instance();

            expect($success)->toBe(true);
            expect($actual)->toBe(null);

        });

        it("returns `false` if there's no patched autoloader", function () {

            Interceptor::patch(['cachePath' => $this->cachePath]);
            Interceptor::unpatch();

            $success = Interceptor::unpatch();
            expect($success)->toBe(false);

        });

    });

    describe("::load()", function () {

        it("auto unpatch when loading an interceptor autoloader", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $new = new Interceptor([
                'originalLoader' => $interceptor->originalLoader(),
                'cachePath'      => $this->cachePath
            ]);

            Interceptor::load($new);

            expect(Interceptor::instance())->toBe($new);
            expect(Interceptor::instance())->not->toBe($interceptor);

        });

    });

    describe("::instance()", function () {

        it("returns the interceptor autoloader", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);
            expect($interceptor)->toBeAnInstanceOf("Kahlan\Jit\Interceptor");

        });

    });

    describe("::composer()", function () {

        it("returns the composer autoloader", function () {

            $composer = Interceptor::composer()[0];
            expect($composer)->toBeAnInstanceOf("Composer\Autoload\ClassLoader");

        });

    });

    describe("->__construct()", function () {

        it("clear caches if `'clearCache'` is `true`", function () {

            touch($this->cachePath . DS . 'CachedFile.php');

            $this->interceptor = Interceptor::patch([
                'cachePath'  => $this->cachePath,
                'clearCache' => true
            ]);

            expect(file_exists($this->cachePath . DS . 'CachedFile.php'))->toBe(false);

        });

        it("initializes watched files if passed to the constructor", function () {
            $this->temp = Dir::tempnam(null, 'cache');

            touch($this->temp . DS . 'watched1.php');
            touch($this->temp . DS . 'watched2.php');

            $watched = [
                $this->temp . DS . 'watched1.php',
                $this->temp . DS . 'watched2.php'
            ];

            $this->interceptor = Interceptor::patch([
                'cachePath' => $this->cachePath,
                'watch' => $watched
            ]);

            expect($this->interceptor->watched())->toBe($watched);

            Dir::remove($this->temp);
        });

    });

    describe("->findFile()", function () {

        it("deletages finds to patched autoloader", function () {

            Interceptor::patch(['cachePath' => $this->cachePath]);

            expect($this->autoloader)->toReceive('findFile')->with('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA');

            $interceptor = Interceptor::instance();
            $actual = $interceptor->findFile('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA');

        });

        it("still finds path even with no patchers defined", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $expected = realpath('spec/Fixture/Jit/Interceptor/ClassA.php');
            $actual = $interceptor->findFile('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA');

            expect($actual)->toBe($expected);

        });

        context("with some patchers defined", function () {

            beforeEach(function () {

                $this->interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

                $this->patcher1 = new Patcher();
                $this->patcher2 = new Patcher();

                $this->patchers = $this->interceptor->patchers();
                $this->patchers->add('patch1', $this->patcher1);
                $this->patchers->add('patch2', $this->patcher2);

            });

            it("delegates find to patchers", function () {

                allow($this->patcher1)->toReceive('findFile')->andRun(function ($interceptor, $class, $file) {
                    return $file . '1';
                });
                allow($this->patcher2)->toReceive('findFile')->andRun(function ($interceptor, $class, $file) {
                    return $file . '2';
                });

                $expected = realpath('spec/Fixture/Jit/Interceptor/ClassA.php');
                $actual = $this->interceptor->findFile('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA');
                expect($actual)->toBe($expected . '12');

            });

        });

    });

    describe("->loadFile()", function () {

        beforeEach(function () {
            $this->interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);
            $this->loadFileNamespacePath = Dir::tempnam(null, 'loadFileNamespace');
            $this->interceptor->addPsr4('loadFileNamespace\\', $this->loadFileNamespacePath);
            $this->classBuilder = function ($name) {
                return "<?php namespace loadFileNamespace; class {$name} {} ?>";
            };
        });

        afterEach(function () {
            Dir::remove($this->loadFileNamespacePath);
        });

        context("when interceptor doesn't watch additional files", function () {

            it("loads a file", function () {

                $sourcePath = $this->loadFileNamespacePath . DS . 'ClassA.php';
                file_put_contents($sourcePath, $this->classBuilder('ClassA'));

                expect($this->interceptor->loadFile($sourcePath))->toBe(true);
                expect(class_exists('loadFileNamespace\ClassA', false))->toBe(true);

            });

            it("loads cached files", function () {

                $sourcePath = $this->loadFileNamespacePath . DS . 'ClassCached.php';
                $body = $this->classBuilder('ClassCached');
                file_put_contents($sourcePath, $body);
                $sourceTimestamp = filemtime($sourcePath);
                $this->interceptor->cache($sourcePath, $body, $sourceTimestamp + 1);

                expect($this->interceptor->loadFile($sourcePath))->toBe(true);
                expect(class_exists('loadFileNamespace\ClassCached', false))->toBe(true);

            });

            it("throws an exception for unexisting files", function () {

                $path = $this->loadFileNamespacePath . DS . 'ClassUnexisting.php';

                $closure= function () use ($path) {
                    $interceptor = Interceptor::instance();
                    $interceptor->loadFile($path);
                };
                expect($closure)->toThrow("Error, the file `'{$path}'` doesn't exist.");

            });

            it("caches a loaded files and set the cached file motification time to be the same as the source file", function () {

                $sourcePath = $this->loadFileNamespacePath . DS . 'ClassB.php';
                file_put_contents($sourcePath, $this->classBuilder('ClassB'));

                $currentTimestamp = time();
                $sourceTimestamp = $currentTimestamp - 5 * 60;

                touch($sourcePath, $sourceTimestamp);

                expect($this->interceptor->loadFile($sourcePath))->toBe(true);
                expect(class_exists('loadFileNamespace\ClassB', false))->toBe(true);

                $cacheTimestamp = filemtime($this->interceptor->cachePath() . DS . ltrim(preg_replace('~:~', '', $sourcePath), DS));
                expect($sourceTimestamp)->toBe($cacheTimestamp - 1);

            });

        });

        context("when the interceptor watch some additional files", function () {

            beforeEach(function () {
                $this->currentTimestamp = time();
                $this->watched1Timestamp = $this->currentTimestamp - 1 * 60;
                $this->watched2Timestamp = $this->currentTimestamp - 2 * 60;

                touch($this->loadFileNamespacePath . DS . 'watched1.php', $this->watched1Timestamp);
                touch($this->loadFileNamespacePath . DS . 'watched2.php', $this->watched2Timestamp);

                $this->interceptor->watch([
                    $this->loadFileNamespacePath . DS . 'watched1.php',
                    $this->loadFileNamespacePath . DS . 'watched2.php'
                ]);
            });

            it("caches a file and set the cached file motification time to be the max timestamp between the watched and the source file", function () {

                file_put_contents($this->loadFileNamespacePath . DS . 'ClassC.php', $this->classBuilder('ClassC'));

                $sourceTimestamp = $this->currentTimestamp - 5 * 60;

                touch($this->loadFileNamespacePath . DS . 'ClassC.php', $sourceTimestamp);

                expect($this->interceptor->loadFile($this->loadFileNamespacePath . DS . 'ClassC.php'))->toBe(true);
                expect(class_exists('loadFileNamespace\ClassC', false))->toBe(true);

                $cacheTimestamp = filemtime($this->interceptor->cachePath() . DS . ltrim(preg_replace('~:~', '', $this->loadFileNamespacePath), DS) . DS . 'ClassC.php');
                expect($this->watched1Timestamp)->toBe($cacheTimestamp - 1);

            });

        });

    });

    describe("->loadFiles()", function () {

        it("loads a file", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            expect($interceptor->loadFiles([
                'spec/Fixture/Jit/Interceptor/ClassB.php',
                'spec/Fixture/Jit/Interceptor/ClassC.php'
            ]))->toBe(true);
            expect(class_exists('Kahlan\Spec\Fixture\Jit\Interceptor\ClassB', false))->toBe(true);
            expect(class_exists('Kahlan\Spec\Fixture\Jit\Interceptor\ClassC', false))->toBe(true);

        });

    });

    describe("->loadClass()", function () {

        it("loads a class", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            expect($interceptor->loadClass('Kahlan\Spec\Fixture\Jit\Interceptor\ClassD'))->toBe(true);
            expect(class_exists('Kahlan\Spec\Fixture\Jit\Interceptor\ClassD', false))->toBe(true);

        });

        it("bails out the patching process if the class has been excluded from being patched", function () {

            $interceptor = Interceptor::patch([
                'include' => ['allowed\\'],
                'cachePath' => $this->cachePath
            ]);

            $cached = $this->cachePath . realpath('spec/Fixture/Jit/Interceptor/ClassE.php');
            $interceptor->loadClass('Kahlan\Spec\Fixture\Jit\Interceptor\ClassE');
            expect(file_exists($cached))->toBe(false);

        });

        it("loads and proccess patchable class", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);
            $patcher = new Patcher();
            $interceptor->patchers()->add('patcher', $patcher);

            expect($interceptor->loadClass('Kahlan\Spec\Fixture\Jit\Interceptor\ClassF'))->toBe(true);
            expect(class_exists('Kahlan\Spec\Fixture\Jit\Interceptor\ClassF', false))->toBe(true);

        });

        it("returns null when the class can't be loaded", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            expect($interceptor->loadClass('Kahlan\Spec\Fixture\Jit\Interceptor\ClassUnexisting'))->toBe(null);

        });

    });

    describe("->findPath()", function () {

        it("finds a namespace path", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $expected = realpath('spec/Fixture/Jit/Interceptor');
            expect($interceptor->findPath('Kahlan\Spec\Fixture\Jit\Interceptor'))->toBe($expected);

        });

        it("finds a PHP class path", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $expected = realpath('spec/Fixture/Jit/Interceptor/ClassA.php');
            expect($interceptor->findPath('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA'))->toBe($expected);

        });

        it("finds a HH class path", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $expected = realpath('spec/Fixture/Jit/Interceptor/ClassHh.hh');
            expect($interceptor->findPath('Kahlan\Spec\Fixture\Jit\Interceptor\ClassHh'))->toBe($expected);

        });

        it("gives precedence to files", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $expected = realpath('spec/Fixture/Jit/Interceptor/ClassA.php');
            expect($interceptor->findPath('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA'))->toBe($expected);

        });

        it("forces the returned path to be a directory", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $expected = realpath('spec/Fixture/Jit/Interceptor/ClassA');
            expect($interceptor->findPath('Kahlan\Spec\Fixture\Jit\Interceptor\ClassA', true))->toBe($expected);

        });

    });

    describe("->__call()", function () {

        it("deletages calls to patched autoloader", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            expect($this->autoloader)->toReceive('getClassMap');

            $interceptor->getClassMap();

        });

    });

    describe("->allowed()", function () {

        it("returns true by default", function () {

            $interceptor = Interceptor::patch([
                'include' => ['*'],
                'cachePath' => $this->cachePath
            ]);

            $actual = $interceptor->allowed('anything\namespace\SomeClass');
            expect($actual)->toBe(true);

        });

        it("returns true if the class match the include", function () {

            $interceptor = Interceptor::patch([
                'include' => ['allowed\\'],
                'cachePath' => $this->cachePath
            ]);

            $allowed = $interceptor->allowed('allowed\namespace\SomeClass');
            $notallowed = $interceptor->allowed('notallowed\namespace\SomeClass');

            expect($allowed)->toBe(true);
            expect($notallowed)->toBe(false);

        });

        it("processes exclude first", function () {

            $interceptor = Interceptor::patch([
                'exclude' => ['namespace\\notallowed\\'],
                'include' => ['namespace\\'],
                'cachePath' => $this->cachePath
            ]);

            $allowed = $interceptor->allowed('namespace\allowed\SomeClass');
            $notallowed = $interceptor->allowed('namespace\notallowed\SomeClass');

            expect($allowed)->toBe(true);
            expect($notallowed)->toBe(false);

        });

    });

    describe("->cachePath()", function () {

        it("returns the cache path", function () {

            $interceptor = Interceptor::patch();

            $path = $interceptor->cachePath();

            expect($path)->toBe(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jit');

        });

    });

    describe("->cache()", function () {

        it("throws an exception if no cache has been disabled", function () {

            $this->temp = Dir::tempnam(null, 'cache');

            $closure = function () {
                $interceptor = Interceptor::patch(['cachePath' => false]);
                $interceptor->cache($this->temp . DS . 'ClassToCache.php', '');
            };

            expect($closure)->toThrow(new JitException('Error, any cache path has been defined.'));

            Dir::remove($this->temp);
        });

        context("with a valid cache path", function () {

            beforeEach(function () {
                $this->interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);
                $this->temp = Dir::tempnam(null, 'cache');

            });

            afterEach(function () {
                Dir::remove($this->temp);
            });

            it("caches a file and into a subtree similar to the source location", function () {

                $path = $this->temp . DS . 'ClassToCache.php';
                $cached = $this->interceptor->cache($path, '');
                expect($cached)->toBe($this->interceptor->cachePath() . DS . ltrim(preg_replace('~:~', '', $path), DS));

            });

        });

    });

    describe("->cached()", function () {

        it("returns false when trying to get an unexisting file", function () {

            $interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

            $cached = $interceptor->cached('an\arbitrary\path\ClassUnexisting');

            expect($cached)->toBe(false);

        });

        it("returns false when trying no cache path has been defined", function () {

            $interceptor = Interceptor::patch(['cachePath' => false]);

            $cached = $interceptor->cached('an\arbitrary\path\ClassUnexisting');

            expect($cached)->toBe(false);

        });

        context("when the interceptor doesn't watch some additional files", function () {

            beforeEach(function () {
                $this->interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

                $this->temp = Dir::tempnam(null, 'cache');
                $this->cached = $this->interceptor->cache($this->temp . DS . 'CachedClass.php', '');
            });

            afterEach(function () {
                Dir::remove($this->temp);
            });

            it("returns the cached file path if the modified timestamp of the cached file is up to date", function () {
                touch($this->temp . DS . "CachedClass.php", time() - 1);
                expect($this->interceptor->cached($this->temp . DS . 'CachedClass.php'))->not->toBe(false);
            });

            it("returns false if the modified timestamp of the cached file is outdated", function () {
                touch($this->temp . DS . "CachedClass.php", time() + 1);
                expect($this->interceptor->cached($this->temp . DS . 'CachedClass.php'))->toBe(false);
            });

        });

        context("when the interceptor watch some additional files", function () {

            beforeEach(function () {
                $this->interceptor = Interceptor::patch(['cachePath' => $this->cachePath]);

                $this->temp = Dir::tempnam(null, 'cache');
                $this->cached = $this->interceptor->cache($this->temp . DS . 'CachedClass.php', '');
            });

            afterEach(function () {
                Dir::remove($this->temp);
            });

            it("returns the cached file path if the modified timestamp of the cached file is up to date", function () {

                $time = time();
                touch($this->temp . DS . 'watched1.php', $time - 1);
                touch($this->temp . DS . 'watched2.php', $time - 1);
                touch($this->temp . DS . 'CachedClass.php', $time - 1);

                $this->interceptor->watch([$this->temp . DS . 'watched1.php', $this->temp . DS . 'watched2.php']);

                expect($this->interceptor->cached($this->temp . DS . 'CachedClass.php'))->not->toBe(false);
            });

            it("returns false if the modified timestamp of the cached file is outdated", function () {

                $time = time();
                touch($this->temp . DS . 'watched1.php', $time - 1);
                touch($this->temp . DS . 'watched2.php', $time - 1);
                touch($this->temp . DS . 'CachedClass.php', $time + 1);

                $this->interceptor->watch([$this->temp . DS . 'watched1.php', $this->temp . DS . 'watched2.php']);

                expect($this->interceptor->cached($this->temp . DS . 'CachedClass.php'))->toBe(false);
            });

            it("returns false if the modified timestamp of a watched file is outdated", function () {

                $time = time();
                touch($this->temp . DS . 'watched1.php', $time - 1);
                touch($this->temp . DS . 'watched2.php', $time + 1);
                touch($this->temp . DS . 'CachedClass.php', $time - 1);

                $this->interceptor->watch([$this->temp . DS . 'watched1.php', $this->temp . DS . 'watched2.php']);

                expect($this->interceptor->cached($this->temp . DS . 'CachedClass.php'))->toBe(false);

                touch($this->temp . DS . 'watched1.php', $time + 1);
                touch($this->temp . DS . 'watched2.php', $time - 1);
                touch($this->temp . DS . 'CachedClass.php', $time - 1);

                $this->interceptor->watch([$this->temp . DS . 'watched1.php', $this->temp . DS . 'watched2.php']);

                expect($this->interceptor->cached($this->temp . DS . 'CachedClass.php'))->toBe(false);
            });

        });

    });

    describe("->clearCache()", function () {

        beforeEach(function () {
            $this->customCachePath = Dir::tempnam(null, 'cache');
            $this->interceptor = Interceptor::patch(['cachePath' => $this->customCachePath]);

            $this->temp = Dir::tempnam(null, 'cache');
            $this->interceptor->cache($this->temp . DS . 'CachedClass1.php', '');
            $this->interceptor->cache($this->temp . DS . 'nestedDir/CachedClass2.php', '');
        });

        afterEach(function () {
            Dir::remove($this->temp);
        });

        it("clears the cache", function () {

            $this->interceptor->clearCache();
            expect(file_exists($this->customCachePath))->toBe(false);

        });

        it("bails out if the cache has already been cleared", function () {

            $this->interceptor->clearCache();
            $this->interceptor->clearCache();
            expect(file_exists($this->customCachePath))->toBe(false);

        });

    });

    describe("->watch()/unwatch()", function () {

        it("add some file to be watched", function () {
            $this->temp = Dir::tempnam(null, 'cache');

            touch($this->temp . DS . 'watched1.php');
            touch($this->temp . DS . 'watched2.php');

            $watched = [
                $this->temp . DS . 'watched1.php',
                $this->temp . DS . 'watched2.php'
            ];

            $this->interceptor = Interceptor::patch([
                'cachePath' => $this->cachePath
            ]);

            $this->interceptor->watch($this->temp . DS . 'watched1.php');
            expect($this->interceptor->watched())->toBe([$watched[0]]);

            $this->interceptor->watch($this->temp . DS . 'watched2.php');
            expect($this->interceptor->watched())->toBe($watched);

            $this->interceptor->unwatch($this->temp . DS . 'watched1.php');
            expect($this->interceptor->watched())->toBe([$watched[1]]);

            $this->interceptor->unwatch($this->temp . DS . 'watched2.php');
            expect($this->interceptor->watched())->toBe([]);

            Dir::remove($this->temp);
        });

    });

});
