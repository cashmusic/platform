# The CASH Music Test Suite

This is the CASHMusic test suite.

Currently we have PHP unit tests, which live in the tests/php/ directory and
Perl 5 integration tests in tests/integration .
Our PHP tests are written using [SimpleTest](http://www.simpletest.org/) and
our Perl tests use [Test::WWW::Mechanize](http://p3rl.org/Test::WWW::Mechanize/).

JS tests are coming Real Soon Now.

# Running the PHP tests

## Running an individual test file

If you just wrote a new test, you probably just want to run that single test
file, first.

From the "root" of this repo (one directory up from here), run

    php tests/php/run-tests.php foobar

where the string foobar appears in the filename of the test(s) that you want to
run.

## Running the entire test suite

Before you send a pull request, please run the full test suite:

    php tests/php/all.php

to make sure your changes have not accidentally broken someting.
When the tests pass, you should see something like this at the end:

    OK
    Test cases run: 3/3, Passes: 43, Failures: 0, Exceptions: 0

Recent [Test Suite Results](http://dev.cashmusic.org:3000/project/DIY)
