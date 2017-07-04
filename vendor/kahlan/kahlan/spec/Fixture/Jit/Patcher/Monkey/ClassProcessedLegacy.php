<?php
namespace Kahlan\Spec\Fixture\Jit\Patcher\Monkey;$__KMONKEY__21__=null;$__KMONKEY__21=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'Exemple', false, $__KMONKEY__21__);$__KMONKEY__22=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'time');

use Kahlan\MongoId;
use Kahlan\Util\Text;
use sub\name\space;

function time() {
    return 0;
}

class Example extends \Kahlan\Fixture\Parent
{
    use A, B {
        B::smallTalk insteadof A;
        A::bigTalk insteadof B;
    }

    public $type = User::TYPE;

    public function classic()
    {$__KMONKEY__0=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'rand');
        $__KMONKEY__0(2, 5);
    }

    public function rootBased()
    {$__KMONKEY__1=\Kahlan\Plugin\Monkey::patched(null , 'rand');
        $__KMONKEY__1(2, 5);
    }

    public function nested()
    {$__KMONKEY__2=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'rand');
        return $__KMONKEY__2($__KMONKEY__2(2, 5), $__KMONKEY__2(6, 10));
    }

    public function inString()
    {
        'rand(2, 5)';
    }

    public function namespaced()
    {$__KMONKEY__3=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'time');
        $__KMONKEY__3();
    }

    public function rootBasedInsteadOfNamespaced()
    {$__KMONKEY__4=\Kahlan\Plugin\Monkey::patched(null , 'time');
        $__KMONKEY__4();
    }

    public function instantiate()
    {$__KMONKEY__5__=null;$__KMONKEY__5=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'stdClass', false, $__KMONKEY__5__);
        new $__KMONKEY__5;
    }

    public function instantiateWithArguments()
    {$__KMONKEY__6__=null;$__KMONKEY__6=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'PDO', false, $__KMONKEY__6__);
        $this->_db = new $__KMONKEY__6(
            "mysql:dbname=testdb;host=localhost",
            'root',
            ''
        );
    }

    public function instantiateRootBased()
    {$__KMONKEY__7__=null;$__KMONKEY__7=\Kahlan\Plugin\Monkey::patched(null , 'stdClass', false, $__KMONKEY__7__);
        new $__KMONKEY__7;
    }

    public function instantiateFromUsed()
    {$__KMONKEY__8__=null;$__KMONKEY__8=\Kahlan\Plugin\Monkey::patched(null, 'Kahlan\MongoId', false, $__KMONKEY__8__);
        new $__KMONKEY__8;
    }

    public function instantiateRootBasedFromUsed()
    {$__KMONKEY__9__=null;$__KMONKEY__9=\Kahlan\Plugin\Monkey::patched(null , 'MongoId', false, $__KMONKEY__9__);
        new $__KMONKEY__9;
    }

    public function instantiateFromUsedSubnamespace()
    {$__KMONKEY__10__=null;$__KMONKEY__10=\Kahlan\Plugin\Monkey::patched(null, 'sub\name\space\MyClass', false, $__KMONKEY__10__);
        new $__KMONKEY__10;
    }

    public function instantiateVariable()
    {
        $class = 'MongoId';
        new $class;
    }

    public function staticCall()
    {$__KMONKEY__11__=null;$__KMONKEY__11=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'Debugger', false, $__KMONKEY__11__);
        return $__KMONKEY__11::trace();
    }

    public function staticCallFromUsed()
    {$__KMONKEY__12__=null;$__KMONKEY__12=\Kahlan\Plugin\Monkey::patched(null, 'Kahlan\Util\Text', false, $__KMONKEY__12__);
        return $__KMONKEY__12::hash((object) 'hello');
    }

    public function staticCallAndinstantiation() {$__KMONKEY__13__=null;$__KMONKEY__13=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'Parser', false, $__KMONKEY__13__);
        $node = $__KMONKEY__13::parse($string);
        return new $__KMONKEY__13($node);
    }

    public function noIndent()
    {$__KMONKEY__14=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'rand');
$__KMONKEY__14();
    }

    public function closure()
    {
        $func = function() {$__KMONKEY__15=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'rand');
            $__KMONKEY__15(2.5);
        };
        $func();
    }

    public function staticAttribute()
    {
        $type = User::TYPE;
    }

    public function lambda()
    {
        $initializers = [
            'name' => function($self) {$__KMONKEY__16=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'str_replace');$__KMONKEY__17=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'basename');
                return $__KMONKEY__17($__KMONKEY__16('\\', '/', $self));
            },
            'source' => function($self) {$__KMONKEY__18__=null;$__KMONKEY__18=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'Inflector', false, $__KMONKEY__18__);
                return $__KMONKEY__18::tableize($self::meta('name'));
            },
            'title' => function($self) {$__KMONKEY__19=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'array_merge');
                $titleKeys = array('title', 'name');
                $titleKeys = $__KMONKEY__19($titleKeys, (array) $self::meta('key'));
                return $self::hasField($titleKeys);
            }
        ];
    }

    public function subChild() {$__KMONKEY__20__=null;$__KMONKEY__20=\Kahlan\Plugin\Monkey::patched(__NAMESPACE__ , 'RecursiveIteratorIterator', false, $__KMONKEY__20__);
        if ($options['recursive']) {
            $worker = new $__KMONKEY__20($worker, $iteratorFlags);
        }
    }

    public function ignoreControlStructure()
    {
        array();
        true and(true);
        try{} catch (\Exception $e) {};
        clone();
        compact();
        declare(ticks=1);
        die();
        echo('');
        empty($a);
        eval('');
        exit(-1);
        extract();
        for($i=0;$i<1;$i++) {};
        foreach($array as $key=>$value) {}
        func_get_arg();
        func_get_args();
        func_num_args();
        function(){};
        if(true){}
        include('filename');
        include_once('filename');
        if(false){} elseif(true) {}
        isset($a);
        list($a, $b) = ['A', 'B'];
        true or(true);
        new self();
        new static();
        parent::hello();
        print('hello');
        require('filename');
        require_once('filename');
        return($a);
        switch($case){
            case (true && true):
                break;
            default:
        }
        throw($e);
        unset($a);
        while(false){};
        true xor(true);
    }

    public function ignoreControlStructureInUpperCase()
    {
        ARRAY();
        TRUE AND(TRUE);
        TRY{} CATCH (\EXCEPTION $E) {};
        COMPACT();
        DECLARE(TICKS=1);
        DIE();
        ECHO('');
        EMPTY($A);
        EVAL('');
        EXIT(-1);
        EXTRACT();
        FOR($I=0;$I<1;$I++) {};
        FOREACH($ARRAY AS $KEY=>$VALUE) {}
        FUNCTION(){};
        IF(TRUE){}
        INCLUDE('FILENAME');
        INCLUDE_ONCE('FILENAME');
        IF(FALSE){} ELSEIF(TRUE) {}
        ISSET($A);
        LIST($A, $B) = ['A', 'B'];
        TRUE OR(TRUE);
        NEW SELF();
        NEW STATIC();
        PARENT::HELLO();
        PRINT('HELLO');
        REQUIRE('FILENAME');
        REQUIRE_ONCE('FILENAME');
        RETURN($A);
        SWITCH($CASE){
            CASE (TRUE && TRUE):
                BREAK;
            DEFAULT:
        }
        UNSET($A);
        WHILE(FALSE){};
        TRUE XOR(TRUE);
    }
}

$__KMONKEY__21::reset();
$time = $__KMONKEY__22();
