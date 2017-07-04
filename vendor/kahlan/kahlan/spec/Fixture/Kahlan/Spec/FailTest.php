<?php
$suite = $this->suite();

$suite->describe("Fail", function() {

	$this->it("fail", function() {

		$this->expect(true)->toBe(false);

	});

});
