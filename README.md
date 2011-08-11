# CASH Music Platform / DIY

The CASH Music Platform is very much still a work in progress in its pre-release
lifecycle.

The DIY version of the CASH platform is a locally-installable API intended to
work in a request/response model similar to a REST implementation and handle
complex functionality around the marketing, promotion, and sale of music on an
artist's site. It is designed to work as a freestanding API or integrated with
publishing and CMS systems like Wordpress or Drupal.

# Notes

Most of the code is currently found in framework/local/php/ and centers around a
single-include workflow. The Seed.php does some basic housekeeping before
firing up a CASHRequest instance that parses an incoming request. That request
is passed to the appropriate Plant (factory) class which then figures out what
to do with the request and fires off any necessary Seed (worker) classes. When
done the Plant returns any information in a standard CASHResponse format which
is stored in a standardized session variable and in the original CASHRequest.

So something like this:

    CASHRequest->Plant->(Seed(s) if needed)->CASHResponse

Long-term goal is to use standardized requests/responses to enable full action
chaining for new functionality.

# Hacking on CASH Music

To hack on CASH Music DIY, first grab the git repo:

    git clone git://github.com/cashmusic/DIY.git

If you are behind a pesky firewall, you might need to use https, but only do
this if you must, since it is much slower and uses up more bandwidth:

    git clone https://leto@github.com/cashmusic/DIY.git

We have a test suite to make sure that things don't break when we add features
and fix bugs. Currently is it small and only syntax checks all the PHP files,
but soon it will be a beautiful butterfly.

To run the CASH Music tests, run this command from the root directory of the
CASH Music git repo:

    cd DIY
    php tests/php/all.php

When the tests pass, you should see something like this at the end:

    OK
    Test cases run: 3/3, Passes: 43, Failures: 0, Exceptions: 0

Our tests are written using [SimpleTest](http://www.simpletest.org/).

# License

CashMusic DIY is (c) 2010 CASH Music, licensed under a AGPL license:
<http://www.gnu.org/licenses/agpl-3.0.html>
