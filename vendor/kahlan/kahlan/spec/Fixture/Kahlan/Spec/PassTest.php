<?php
$suite = $this->suite();

$suite->describe("Pass", function() {

	$this->it("pass", function() {

		$this->expect(true)->toBe(true);

	});

});
