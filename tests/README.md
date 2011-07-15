# CashMusic Tests

These are the CashMusic tests, which make sure that everything works as it should.

Currently there are only PHP tests, which all live in the php/ directory.

# Running the PHP tests

## Running an individual test file

If you just wrote a new test, you probably just want to run that single test file, first.

From the "root" of this repo (one directory up from here), run

    php tests/php/001_BasicTests.php

## Running the entire test suite

Before you send a pull request, it is best to see if any new code introduced bugs by
running the full test suite:

    php tests/php/all.php
