![CASH Music Test Suite](https://cashmusic.s3.amazonaws.com/permalink/images/readme_testsuite.jpg)

Currently we have PHP unit tests and integration tests, which live in the tests/php/ directory.

Our tests are written using [SimpleTest](http://www.simpletest.org/) and run locally as well as 
continuously on [Travis-CI](http://travis-ci.org/cashmusic/platform).

[![Build Status](https://secure.travis-ci.org/cashmusic/platform.png)](http://travis-ci.org/cashmusic/platform)


## Running the PHP tests

Before you send a pull request, please run the full test suite:

    make test

to make sure your changes have not accidentally broken something.
When the tests pass, you should see something like this at the end:

    OK
    Test cases run: 3/3, Passes: 430, Failures: 0, Exceptions: 0

  
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