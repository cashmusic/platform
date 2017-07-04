<?php
namespace Kahlan\Spec\Jit\Suite;

use Exception;
use Kahlan\Jit\TokenStream;

describe("TokenStream", function () {

    beforeEach(function () {
        $this->code = <<<EOD
<?php
class HelloWorld
{
    public function hello() {
        \$echo = function(\$msg) { echo \$msg };
        \$echo('Hello World');
    }
}
?>
EOD;
        $this->stream = new TokenStream(['source' => $this->code]);
        $this->len = count(token_get_all($this->code));
    });

    describe("->load", function () {

        it("wraps passed code with PHP tags when the `'wrap'` option is used", function () {

            $stream = new TokenStream([
                'source' => 'class HelloWorld {}',
                'wrap'   => true
            ]);
            $actual = $stream->current();
            expect($actual)->toBe("class");

        });

    });

    describe("->current()", function () {

        it("gets the current token value", function () {

            $actual = $this->stream->current();
            expect($actual)->toBe("<?php\n");

        });

        it("gets the current token", function () {

            $actual = $this->stream->current(true);
            expect($actual)->toBe([T_OPEN_TAG, "<?php\n", 1]);

        });

    });

    describe("->next()", function () {

        it("moves next", function () {

            $key = $this->stream->key();
            $this->stream->next();
            expect($key)->not->toBe($this->stream->key());

        });

        it("gets the next token value", function () {

            $actual = $this->stream->next();
            expect($actual)->toBe("class");

        });

        it("gets the next token", function () {

            $actual = $this->stream->next(true);
            expect($actual)->toBe([T_CLASS, "class", 2]);

        });

        it("iterates through all tokens", function () {

            $i = 0;
            foreach ($this->stream as $value) {
                $len = strlen($value);
                expect($value)->toBe(substr($this->code, $i, $len));
                $i += $len;
            }
            expect($i)->toBe(strlen($this->code));

        });

        it("returns the skipped content until the next correponding token", function () {
            $actual = $this->stream->next(T_CLASS);
            expect($actual)->toBe("class");
        });

        it("cancels the lookup if the token is not found", function () {
            $actual = $this->stream->next(999);
            expect($actual)->toBe(null);

            $actual = $this->stream->next(T_CLASS);
            expect($actual)->toBe("class");
        });

    });

    describe("->nextSequence()", function () {

        it("moves to the next sequence", function () {

            $actual = $this->stream->nextSequence('()');
            expect($actual)->toBe("class HelloWorld\n{\n    public function hello()");

        });

        it("cancels the lookup if the token is not found", function () {
            $actual = $this->stream->nextSequence('()()');
            expect($actual)->toBe(null);

            $actual = $this->stream->next(T_CLASS);
            expect($actual)->toBe("class");
        });

    });

    describe("->nextMatchingBracket()", function () {

        it("extracts the body between two correponding bracket", function () {
            $stream = new TokenStream(['source' => '<?php { rand(2,5); } ?>']);
            $stream->next('{');
            $actual = $stream->nextMatchingBracket();
            expect($actual)->toBe("{ rand(2,5); }");
        });

        it("supports nested brackets", function () {

            $stream = new TokenStream(['source' => '<?php { { } } ?>']);
            $stream->next('{');
            $actual = $stream->nextMatchingBracket();
            expect($actual)->toBe('{ { } }');

        });

        it("bails out nicely if there's no further tags", function () {

            $stream = new TokenStream(['source' => '']);
            $actual = $stream->nextMatchingBracket();
            expect($actual)->toBe(null);

        });

        it("bails out nicely if the current tags is not an open tags", function () {

            $stream = new TokenStream(['source' => '<?php ?>']);
            $actual = $stream->nextMatchingBracket();
            expect($actual)->toBe(null);

        });

        it("cancels the lookup if there's no closing tags", function () {

            $stream = new TokenStream(['source' => '<?php { { } ?>']);
            $stream->next('{');
            $actual = $stream->nextMatchingBracket();
            expect($actual)->toBe(null);
            expect($stream->getValue())->toBe('{');

        });

    });

    describe("->prev()", function () {

        it("moves prev", function () {

            $key = $this->stream->key();
            $this->stream->next();
            $this->stream->prev();
            expect($key)->not->toBe($this->stream->current());

        });

        it("gets the previous token value", function () {

            $this->stream->seek(1);
            $actual = $this->stream->prev();
            expect($actual)->toBe("<?php\n");

        });

        it("gets the previous token", function () {

            $this->stream->seek(1);
            $actual = $this->stream->prev(true);
            expect($actual)->toBe([T_OPEN_TAG, "<?php\n", 1]);

        });

    });

    describe("->key()", function () {

        it("returns the current key", function () {

            expect($this->stream->key())->toBe(0);
            $this->stream->next();
            expect($this->stream->key())->toBe(1);

        });

    });

    describe("->seek()", function () {

        it("correctly seeks inside the stream", function () {

            $this->stream->seek($this->len - 1);
            expect('?>')->toBe($this->stream->current());

        });

    });

    describe("->rewind()", function () {

        it("resets the stream to the start", function () {

            $key = $this->stream->key();
            $this->stream->next();
            $this->stream->rewind();
            expect($key)->toBe($this->stream->key());

        });

    });

    describe("->valid()", function () {

        it("returns true if the the stream is iteratable", function () {

            expect($this->stream->valid())->toBe(true);

        });

        it("returns false if the the stream is no more iteratable", function () {

            $this->stream->seek($this->len - 1);
            expect($this->stream->valid())->toBe(true);
            $this->stream->next();
            expect($this->stream->valid())->toBe(false);

        });

    });

    describe("->count()", function () {

        it("returns the correct number of tokens", function () {

            expect($this->stream->count())->toBe($this->len);

        });

    });

    describe("->offsetGet()", function () {

        it("accesses token by key", function () {

            $key = $this->stream->key();
            $value = $this->stream[$key][1];
            expect($value)->toBe($this->stream->current());

        });

    });

    describe("->offsetExist()", function () {

        it("returns true for an existing offset", function () {

            expect(isset($this->stream[0]))->toBe(true);
            expect(isset($this->stream[$this->len - 1]))->toBe(true);

        });

        it("returns false for an unexisting offset", function () {

            expect(isset($this->stream[$this->len]))->toBe(false);

        });

    });

    describe("->offsetSet()", function () {

        it("throws an exception", function () {

            expect(isset($this->stream[0]))->toBe(true);
            expect(isset($this->stream[$this->len - 1]))->toBe(true);

        });

    });

    describe("->offsetUnset()", function () {

        it("throws an exception", function () {

            expect(function () {
                unset($this->stream[0]);
            })->toThrow(new Exception);

        });

    });

    describe("->offsetSet()", function () {

        it("throws an exception", function () {

            expect(function () {
                $this->stream[0] = [];
            })->toThrow(new Exception());

        });

    });

    describe("->getType()", function () {

        it("returns the correct token type", function () {

            expect($this->stream->getType(0))->toBe(T_OPEN_TAG);

        });

        it("returns the current token type", function () {

            expect($this->stream->getType())->toBe(T_OPEN_TAG);

        });

    });

    describe("->getValue()", function () {

        it("returns the correct token value", function () {

            expect($this->stream->getValue(0))->toBe("<?php\n");

        });

        it("returns the current token type", function () {

            expect($this->stream->getValue())->toBe("<?php\n");

        });

    });

    describe("->getName()", function () {

        it("returns the correct token name", function () {

            expect($this->stream->getName(0))->toBe("T_OPEN_TAG");

        });

    });

    describe("->is()", function () {

        it("returns true when type is correct", function () {

            expect($this->stream->is(T_OPEN_TAG, 0))->toBe(true);

        });

        it("returns false when type is incorrect", function () {

            expect($this->stream->is(T_OPEN_TAG, 1))->toBe(false);

        });

    });

    describe("->__toString()", function () {

        it("generates a string representation of the stream", function () {

            $actual = (string) $this->stream;
            expect($actual)->toBe($this->code);

        });
    });

});
