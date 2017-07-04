<?php
namespace Kahlan\Spec\Fixture\Analysis;

use Exception;

class SampleClass {

    public function parametersExample($a, $b = 100, $c = 'abc', $d = null) {
    }

    public function parameterByReference(&$a) {
    }

    public function exceptionTypeHint(Exception $e) {
    }

    public function arrayTypeHint(array $values) {
    }

    public function callableTypeHint(callable $closure) {
    }

}
