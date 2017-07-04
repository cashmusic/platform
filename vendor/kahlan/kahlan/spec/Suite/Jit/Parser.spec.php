<?php
namespace Kahlan\Spec\Jit\Suite;

use Kahlan\Jit\Parser;

describe("Parser", function () {

    beforeEach(function () {
        $this->flattenTree = function ($nodes, $self) {
            $result = [] ;
            foreach ($nodes as $node) {
                if (count($node->tree)) {
                    $result = array_merge($result, $self->flattenTree($node->tree, $self));
                } else {
                    $result[] = $node;
                }
            }
            return $result;
        };
    });

    describe("->parse()", function () {

        it("parses consistently", function () {

            $sample = file_get_contents('spec/Fixture/Jit/Parser/Sample.php');
            $parsed = Parser::parse($sample);
            expect(Parser::unparse($parsed))->toBe($sample);

        });

        it("parses syntaxically broken use statement and doesn't crash", function () {

            $code = "<?php use MyClass?>";
            $parsed = Parser::parse($code);
            expect(Parser::unparse($parsed))->toBe($code);

        });

        it("parses functions", function () {

            $sample = file_get_contents('spec/Fixture/Jit/Parser/Function.php');
            $root = Parser::parse($sample);
            foreach ($root->tree as $node) {
                if ($node->type === 'function') {
                    expect($node->name)->toBe('myFunction');
                    expect($node->isClosure)->toBe(false);
                    expect($node->isMethod)->toBe(false);
                    expect($node->isGenerator)->toBe(false);
                    expect($node->parent)->toBe($root);
                    expect($node->args)->toBe([
                        '$required',
                        '$param'    => '"value"',
                        '$boolean'  => 'false',
                        '$array'    => '[]',
                        '$array2'   => 'array()',
                        '$constant' => 'PI'
                    ]);
                }
            }

        });

        it("parses PHP directly when the `'php'` option is set to true", function () {

            $code = "namespace MyNamespace;";
            $root = Parser::parse($code, ['php' => true]);
            $nodes = $this->flattenTree($root->tree, $this);
            $namespace = current($nodes);
            expect($namespace->type)->toBe('namespace');
            expect(Parser::unparse($root))->toBe($code);

        });

        it("correctly populates the `->inPhp` attribute", function () {

            $sample = file_get_contents('spec/Fixture/Jit/Parser/Sample.php');
            $root = Parser::parse($sample);
            $plain = [];

            foreach ($this->flattenTree($root->tree, $this) as $node) {
                if (!$node->inPhp) {
                    $plain[] = (string) $node;
                }
            }

            expect($plain)->toBe([
                "<?php\n",
                "?>\n",
                "\n<i> Hello World </i>\n\n",
                "<?php\n",
                "?>\n",
                "<?php\n"
            ]);
        });

        it("correctly populates the `->isGenerator` attribute", function () {

            skipIf(version_compare(phpversion(), '5.5', '<'));

            $sample = file_get_contents('spec/Fixture/Jit/Parser/Generator.php');
            $root = Parser::parse($sample);
            foreach ($root->tree as $node) {
                if ($node->type === 'function') {
                    expect($node->name)->toBe('myGenerator');
                    expect($node->isClosure)->toBe(false);
                    expect($node->isMethod)->toBe(false);
                    expect($node->isGenerator)->toBe(true);
                    expect($node->parent)->toBe($root);
                }
            }

        });

    });

    describe("->debug()", function () {

        it("attaches the correct lines", function () {

            $filename = 'spec/Fixture/Jit/Parser/Sample';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses files with no namespace", function () {

            $filename = 'spec/Fixture/Jit/Parser/NoNamespace';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses heredoc", function () {

            $filename = 'spec/Fixture/Jit/Parser/Heredoc';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses nowdoc", function () {

            $filename = 'spec/Fixture/Jit/Parser/Nowdoc';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses strings", function () {

            $filename = 'spec/Fixture/Jit/Parser/String';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses char at syntax", function () {

            $filename = 'spec/Fixture/Jit/Parser/CharAtSyntax';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses closures", function () {

            $filename = 'spec/Fixture/Jit/Parser/Closure';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses switch cases", function () {

            $filename = 'spec/Fixture/Jit/Parser/Switch';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses uses", function () {

            $filename = 'spec/Fixture/Jit/Parser/Uses';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::parse($content);
            expect($parsed->uses)->toBe([
                'A' => 'Kahlan\A',
                'B' => 'Kahlan\B',
                'C' => 'Kahlan\C',
                'F' => 'Kahlan\E',
                'G' => 'Kahlan\E',
                'StandardClass' => 'stdClass',
                'ClassA' => 'Foo\Bar\Baz\ClassA',
                'ClassB' => 'Foo\Bar\Baz\ClassB',
                'ClassD' => 'Foo\Bar\Baz\Fuz\ClassC',
                'functionName1' => 'My\Name\Space\functionName1',
                'func'          => 'My\Name\Space\functionName2',
                'CONSTANT'      => 'My\\Name\\Space\\CONSTANT'
            ]);

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

        });

        it("parses ::class syntax", function () {

            $filename = 'spec/Fixture/Jit/Parser/StaticClassKeyword';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses anonymous class", function () {

            $filename = 'spec/Fixture/Jit/Parser/AnonymousClass';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);

        });

        it("parses extends", function () {

            $sample = file_get_contents('spec/Fixture/Jit/Parser/Extends.php');
            $root = Parser::parse($sample);

            $check = 0;
            ;

            foreach ($root->tree as $node) {
                if ($node->type !== 'namespace') {
                    continue;
                }
                expect($node->name)->toBe('Test');
                foreach ($node->tree as $node) {
                    if ($node->type !== 'class') {
                        continue;
                    }
                    if ($node->name === 'A') {
                        expect($node->extends)->toBe('\Space\ParentA');
                        $check++;
                    }
                    if ($node->name === 'B') {
                        expect($node->extends)->toBe('\Some\Name\Space\ParentB');
                        $check++;
                    }
                    if ($node->name === 'C') {
                        expect($node->extends)->toBe('\Some\Name\Space');
                        $check++;
                    }
                    if ($node->name === 'D') {
                        expect($node->extends)->toBe('\Test\ParentD');
                        $check++;
                    }
                    if ($node->name === 'E') {
                        expect($node->extends)->toBe('');
                        $check++;
                    }
                }
            }

            expect($check)->toBe(5);
        });

        it("parses implements", function () {

            $sample = file_get_contents('spec/Fixture/Jit/Parser/Implements.php');
            $root = Parser::parse($sample);

            $check = 0;
            ;

            foreach ($root->tree as $node) {
                if ($node->type !== 'namespace') {
                    continue;
                }
                expect($node->name)->toBe('Test');
                foreach ($node->tree as $node) {
                    if ($node->type !== 'class') {
                        continue;
                    }
                    if ($node->name === 'A') {
                        expect($node->implements)->toBe(['\Space\ParentA']);
                        $check++;
                    }
                    if ($node->name === 'B') {
                        expect($node->implements)->toBe(['\Some\Name\Space\ParentB']);
                        $check++;
                    }
                    if ($node->name === 'C') {
                        expect($node->implements)->toBe(['\Test\ParentC']);
                        $check++;
                    }
                    if ($node->name === 'D') {
                        expect($node->implements)->toBe(['\Test\ParentD1', '\Test\ParentD2', '\Test\ParentD3']);
                        $check++;
                    }
                    if ($node->name === 'E') {
                        expect($node->implements)->toBe([]);
                        $check++;
                    }
                }
            }

            expect($check)->toBe(5);
        });

        it("parses declare", function () {

            $filename = 'spec/Fixture/Jit/Parser/Declare';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);
        });

        it("parses declare as block", function () {

            $filename = 'spec/Fixture/Jit/Parser/DeclareAsBlock';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);
        });

        it("parses interfaces", function () {

            $filename = 'spec/Fixture/Jit/Parser/Interface';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);
        });

        it("parses alternative control structures as dead code", function () {

            $filename = 'spec/Fixture/Jit/Parser/AlternativeControlStructures';
            $content = file_get_contents($filename . '.php');

            $parsed = Parser::debug($content);
            expect($parsed)->toBe(file_get_contents($filename . '.txt'));

            $parsed = Parser::parse($content);
            expect(Parser::unparse($parsed))->toBe($content);
        });

    });

});
