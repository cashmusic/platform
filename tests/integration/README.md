# CASH Music Integration Tests

These tests actually interact with the CASH Music website on a local port (it
defaults to localhost:80), and emulate the flow of events that admins and end
users will follow.

These tests require that you already have an Apache instance running CASH
Music, they will not spawn a webserver by themselves.

To customize the URL the integration tests run against, set the
CASHMUSIC_TEST_URL environment variable:

   CASHMUSIC_TEST_URL=http://example.com:8000

CASH Music unit tests are written in PHP, since CASH Music is written
in PHP. Our integration tests can be in any language, and since Perl
has some of the best website testing infrastructure, we use Perl.

## Dependencies

You will need the following CPAN modules to run the integration tests:

 * Test::Most
 * Test::WWW::Mechanize
 * Test::JSON

To install these dependencies, you can:

    perl Build.PL
    ./Build installdeps

or, if you have the lovely cpanminus utility:

    cpanm --installdeps .

## Running the CASH Music Integration Tests

    prove -lrv tests/integration/

or with a custom URL:

    CASHMUSIC_TEST_URL=http://localhost:8000 prove -lrv tests/integration/
