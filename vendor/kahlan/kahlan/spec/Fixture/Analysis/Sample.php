<?php
/**
 * Some comments
 */

namespace Kahlan\Spec\Fixture\Analysis;

use Kahlan\A;
use Kahlan\B, Kahlan\C;
use Kahlan\E as F;
use stdObj;

function slice($data, $keys) {
    $removed = array_intersect_key($data, array_fill_keys((array) $keys, true));
    $data = array_diff_key($data, $removed);
    return array($data, $removed);
}

class Sample extends \Kahlan\Fixture\Parent {

    use A, B {
        B::smallTalk insteadof A;
        A::bigTalk insteadof B;
    }

    protected static $_classes = [
        'matcher' => 'Kahlan\Matcher'
    ];

    protected $_matcher = '';

    // Using a tab
    protected    $_public = true;

    protected $_variable = true;

    public function bracketInString() {
        "/^({$pattern})/";
    }

    public function method1($a, $b = array(), $c = [], $d = 0, $f = 'hello') {
    }

    public function method2(
        $a,
        $b = array(),
        $c = [],
        $d = 0,
        $f = 'hello')
    {
        return rand($a * ($d + 1));
    }

    abstract public function abstractMethod();

    final public function finalMethod() {}

    public function inlineComment() {

        $a = 3; //comment

    } // end function

    public function weirdSyntax() {

        foreach ($variable as $key => $value) {}

    $i++;}

    public function phpArray() {

        $array = array("hello");

        $array = array
        (
            "hello"
        );

        $array = array(
            true,
            false,
            null,
            "hello",
            "world",
            "world!",
        );

        $array = [
            true,
            false,
            null,
            "hello",
            "world",
            "world!",
        ];

    }

    public function multilineConditions() {

        return ($a && (
            $b
            ||
            $c . 'a'
        ));

    }

    public function multilineString() {

        return "a" .
               "multiline" .
               "string";

    }

    public function codeEndAfterSemicolonAndBraces() {

        if (!$options['file'])
        {

            throw new RuntimeException("Missing file name");

        }

        return file_put_contents($options['file'], static::export($options));
    }

    public function funkySyntax()
    {
        $this->{"_{$key}"} = "Hello" + $this->{"_{$key}"};

        $this->{
            "_{$key}"
        } = "Hello" + $this->{
            "_{$key}"
        };
    }

}

class
    Sample2
    extends Sample2 {
}

interface Template1
{
    public function setVariable($name, $var);
    public function getHtml($template);
}

trait Template2 {
    public function setVariable($name, $var) {

    }
    public function getHtml($template) {

    }
}

class Dir extends \FilterIterator{
}

//No scope
for($i = 0; $i <= 10; $i++) {
    $rand = rand();
}

?>

<i> Hello World </i>

<?php
/**
 * Some comments2
 */

namespace Kahlan\Spec\Fixture\Parser;

class Sample3 extends Sample2 {
    public function myMethod() {
        return 'Hello World';
    }
}

?>
<?php
namespace Kahlan\Spec\Fixture\Whatever;

class NoPhpEndTag
{

}
