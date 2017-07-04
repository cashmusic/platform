<?php
$a = function() {
    return 'Hello World';
};

$array = [
    true,
    function() {
        return 'Hello World';
    }
] + [
    'hello',
    $a
];

array_filter(
    $array1,
    function($key) {
        return $key === 'Hello World';
    },
    ARRAY_FILTER_USE_KEY
);

array_filter(
    $array1,
    function($value, $key) {
        return $value === 'Hello World';
    }
);

$a();
