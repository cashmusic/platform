# CASH Music Integration Tests

These tests actually interact with the CASH Music website on a local port (it
defaults to localhost:80), and emulate the flow of events that admins and end
users will follow.

To customize the URL the integration tests run against, set the
CASHMUSIC_TEST_URL like this in your .bashrc:

   CASHMUSIC_TEST_URL=http://example.com:8000

CASH Music unit tests are written in PHP, since CASH Music is written
in PHP. Our integration tests can be in any language, and since Perl
has some of the best website testing infrastructure, we use Perl.

## Dependencies

You will need Test::Most and Test::WWW::Mechanize to run these tests.
Instructions for installing those Coming Soon.

## Running the CASH Music Integration Tests

    prove -lrv *.t

or with a custom URL:

    CASHMUSIC_TEST_URL=http://localhost:8000 prove -lrv *.t
