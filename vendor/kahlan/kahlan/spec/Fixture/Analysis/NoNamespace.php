<?php
use Kahlan\Matcher;

class NoNamespace
{
    public function hello()
    {
        return "Hello World!";
    }
}

function test() {
    return "It's a test";
}

if (true) {
    echo "Hello World!";
}

Matcher::register(
	'toBe',
	'Kahlan\Matcher\ToBe'
);

Box::share(
	'kahlan.suite',
	function() {
		return new Suite;
	}
);

?>

Outside PHP Tags

<?php

for($i = 0; $i < 10; $i++) {
    echo "Success";
}

?>
