![CASH Music Test Suite](https://cashmusic.s3.amazonaws.com/permalink/images/readme_testsuite.jpg)

Currently we have PHP unit tests, which live in the tests/php/ directory and
Perl 5 integration tests in tests/integration. Newer integration tests are being 
written in PHP and we'll be porting all others to PHP soon for ease/consistency.

Our PHP tests are written using [SimpleTest](http://www.simpletest.org/) and
our Perl tests use [Test::WWW::Mechanize](http://p3rl.org/Test::WWW::Mechanize/).

We run our own continuous integration server using [Jitterbug](http://jitterbug.pl) 
and also test on [Travis-CI](http://travis-ci.org/cashmusic/DIY).

[![Build Status](https://secure.travis-ci.org/cashmusic/DIY.png)](http://travis-ci.org/cashmusic/DIY)


## Running the PHP tests

Before you send a pull request, please run the full test suite:

    make test

to make sure your changes have not accidentally broken something.
When the tests pass, you should see something like this at the end:

    OK
    Test cases run: 3/3, Passes: 430, Failures: 0, Exceptions: 0


## Running the PERL integration tests

    make integration_test

Running these tests requires that you have certain CPAN modules installed. 
More details can be found in the /tests/integration [README](https://github.com/cashmusic/DIY/blob/master/tests/integration/README.md).


## Running All Tests

If you want to run both the unit and integration tests, then run:

    make fulltest

Recent [Test Suite Results](http://dev.cashmusic.org:3000/project/DIY)


## Requirements

All tests require a running CASH instance using Apache, MAMP, or similar. Some 
service-specific tests like MailChimp or Paypal will require API keys. Those can 
either be set as environment variables or stored in a JSON file. 

To use the JSON option, create a file called __test_environment.json in the 
/tests/php directory. (That's included in our .gitignore so your settings won't 
get caught in a pull request.) 

The format of the JSON should look like: 

```json
{
"CASHMUSIC_TEST_URL":"http://localhost:8888",

"MAILCHIMP_API_KEY":"...",
"MAILCHIMP_LIST_ID":"...",

"PAYPAL_USERNAME":"...",
"PAYPAL_PASSWORD":"...",
"PAYPAL_SIGNATURE":"...",

"S3_BUCKET":"...",
"S3_KEY":"...",
"S3_SECRET":"..."
}
```