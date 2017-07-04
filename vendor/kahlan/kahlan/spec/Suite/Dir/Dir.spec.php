<?php
namespace dir\spec\suite;

use Kahlan\Dir\Dir;
use Exception;

describe("Dir", function () {

    $this->normalize = function ($path) {
        if (!is_array($path)) {
            return str_replace(DS, '/', $path);
        }
        $result = [];
        foreach ($path as $p) {
            $result[] = $this->normalize($p);
        }
        return $result;
    };

    describe("::scan()", function () {

        $sort = function ($files) {
            sort($files);
            return $files;
        };

        beforeEach(function () {
            $this->path = 'spec/Fixture/Dir';
        });

        it("scans files", function () {

            $files = Dir::scan($this->path, [
                'type' => 'file',
                'recursive' => false
            ]);
            expect($this->normalize($files))->toBe(['spec/Fixture/Dir/file1.txt']);

        });

        it("scans and show dots", function () use ($sort) {

            $files = Dir::scan($this->path, [
                'skipDots' => false,
                'recursive' => false
            ]);

            expect($this->normalize($sort($files)))->toBe($sort([
                'spec/Fixture/Dir/.',
                'spec/Fixture/Dir/..',
                'spec/Fixture/Dir/file1.txt',
                'spec/Fixture/Dir/Nested',
                'spec/Fixture/Dir/Extensions'
            ]));

        });

        it("scans and follow symlinks", function () use ($sort) {

            $files = Dir::scan($this->path . DS . 'Extensions', [
                'followSymlinks' => false,
                'recursive' => false
            ]);

            expect($this->normalize($sort($files)))->toBe([
                'spec/Fixture/Dir/Extensions/Childs',
                'spec/Fixture/Dir/Extensions/file.xml',
                'spec/Fixture/Dir/Extensions/index.html',
                'spec/Fixture/Dir/Extensions/index.php'
            ]);

        });

        it("scans files recursively", function () use ($sort) {

            $files = Dir::scan($this->path . DS . 'Nested', [
                'type' => 'file'
            ]);

            expect($this->normalize($sort($files)))->toBe([
                'spec/Fixture/Dir/Nested/Childs/child1.txt',
                'spec/Fixture/Dir/Nested/nested_file1.txt',
                'spec/Fixture/Dir/Nested/nested_file2.txt'
            ]);

        });

        it("scans files & directores recursively", function () use ($sort) {

            $files = Dir::scan($this->path . DS . 'Nested');

            expect($this->normalize($sort($files)))->toBe([
                'spec/Fixture/Dir/Nested/Childs',
                'spec/Fixture/Dir/Nested/Childs/child1.txt',
                'spec/Fixture/Dir/Nested/nested_file1.txt',
                'spec/Fixture/Dir/Nested/nested_file2.txt'
            ]);

        });

        it("scans only leaves recursively", function () use ($sort) {

            $files = Dir::scan($this->path. DS . 'Nested', [
                'leavesOnly' => true
            ]);

            expect($this->normalize($sort($files)))->toBe([
                'spec/Fixture/Dir/Nested/Childs/child1.txt',
                'spec/Fixture/Dir/Nested/nested_file1.txt',
                'spec/Fixture/Dir/Nested/nested_file2.txt'
            ]);

        });

        it("scans txt files recursively", function () use ($sort) {

            skipIf(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

            $files = Dir::scan($this->path, [
                'include' => '*.txt',
                'type' => 'file'
            ]);

            expect($this->normalize($sort($files)))->toBe([
                'spec/Fixture/Dir/Extensions/Childs/child1.txt',
                'spec/Fixture/Dir/Nested/Childs/child1.txt',
                'spec/Fixture/Dir/Nested/nested_file1.txt',
                'spec/Fixture/Dir/Nested/nested_file2.txt',
                'spec/Fixture/Dir/file1.txt'
            ]);

        });

        it("scans non nested txt files recursively", function () use ($sort) {

            skipIf(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

            $files = Dir::scan($this->path, [
                'include' => '*.txt',
                'exclude' => '*Nested*',
                'type' => 'file'
            ]);

            expect($this->normalize($sort($files)))->toBe([
                'spec/Fixture/Dir/Extensions/Childs/child1.txt',
                'spec/Fixture/Dir/file1.txt'
            ]);

        });

        it("throws an exception if the path is invalid", function () {

            $closure = function () {
                Dir::scan('Non/Existing/Path', [
                    'type' => 'file',
                    'recursive' => false
                ]);
            };
            expect($closure)->toThrow(new Exception());

        });

        it("returns itself when the path is a file", function () {

            $files = Dir::scan('spec/Fixture/Dir/file1.txt', [
                'include' => '*.txt',
                'exclude' => '*nested*',
                'type' => 'file'
            ]);
            expect($this->normalize($files))->toBe(['spec/Fixture/Dir/file1.txt']);

        });

    });

    describe("::copy()", function () {

        beforeEach(function () {
            $this->tmpDir = Dir::tempnam(sys_get_temp_dir(), 'spec');
        });

        afterEach(function () {
            Dir::remove($this->tmpDir, ['recursive' => true]);
        });

        it("copies a directory recursively", function () {

            Dir::copy('spec/Fixture/Dir', $this->tmpDir);

            $paths = Dir::scan('spec/Fixture/Dir');

            foreach ($paths as $path) {
                $target = preg_replace('~^spec/Fixture~', '', $path);
                expect(file_exists($this->tmpDir . $target))->toBe(true);
            }

        });

        it("copies a directory recursively but not following symlinks", function () {

            Dir::copy('spec/Fixture/Dir', $this->tmpDir, ['followSymlinks' => false]);

            $paths = Dir::scan('spec/Fixture/Dir');

            foreach ($paths as $path) {
                $target = preg_replace('~^spec/Fixture~', '', $path);
                if ($this->normalize($target) === '/Dir/Extensions/Childs/child1.txt') {
                    expect(file_exists($this->tmpDir . $target))->toBe(false);
                } else {
                    expect(file_exists($this->tmpDir . $target))->toBe(true);
                }
            }

        });

        it("throws an exception if the destination directory doesn't exists", function () {

            $closure = function () {
                Dir::copy('spec/Fixture/Dir', 'Unexisting/Folder');
            };

            expect($closure)->toThrow(new Exception("Unexisting destination path `Unexisting/Folder`."));

        });

    });

    describe("::remove()", function () {

        it("removes a directory recursively", function () {

            $this->tmpDir = Dir::tempnam(sys_get_temp_dir(), 'spec');

            Dir::copy('spec/Fixture/Dir', $this->tmpDir);

            $paths = Dir::scan('spec/Fixture/Dir');

            Dir::remove($this->tmpDir);

            foreach ($paths as $path) {
                $target = preg_replace('~^spec~', '', $path);
                expect(file_exists($this->tmpDir . $target))->toBe(false);
            }

            expect(file_exists($this->tmpDir))->toBe(false);

        });

    });

    describe("::make()", function () {

        beforeEach(function () {
            $this->umask = umask(0);
            $this->tmpDir = Dir::tempnam(sys_get_temp_dir(), 'spec');
        });

        afterEach(function () {
            Dir::remove($this->tmpDir, ['recursive' => true]);
            umask($this->umask);
        });

        it("creates a nested directory", function () {

            skipIf(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

            $path = $this->tmpDir . '/My/Nested/Directory';
            $actual = Dir::make($path);
            expect($actual)->toBe(true);

            expect(file_exists($path))->toBe(true);

            $stat = stat($path);
            $mode = $stat['mode'] & 0777;
            expect($mode)->toBe(0755);

        });

        it("creates a nested directory with a specific mode", function () {

            $path = $this->tmpDir . '/My/Nested/Directory';
            $actual = Dir::make($path, ['mode' => 0777]);
            expect($actual)->toBe(true);

            expect(file_exists($path))->toBe(true);

            $stat = stat($path);
            $mode = $stat['mode'] & 0777;
            expect($mode)->toBe(0777);

        });

        it("creates multiple nested directories in a single call", function () {

            $paths = [
                $this->tmpDir . '/My/Nested/Directory',
                $this->tmpDir . '/Sub/Nested/Directory'
            ];
            $actual = Dir::make($paths);
            expect($actual)->toBe(true);

            foreach ($paths as $path) {
                expect(file_exists($path))->toBe(true);
            }

        });

    });

    describe("::tempnam()", function () {

        it("uses the system temp directory by default", function () {

            $dir = Dir::tempnam(null, 'spec');

            $temp = sys_get_temp_dir();

            expect($this->normalize($dir))->toMatch('~' . $this->normalize($temp) . '/spe~');

            Dir::remove($dir);

        });

    });

});
