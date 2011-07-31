# CASH Music Platform / DIY

The CASH Platform is very much still a work in progress in its pre-release
lifecycle.

The DIY version of the CASH platform is a locally-installable API intended to
work in a request/response model similar to a REST implementation and handle
complex functionality around the marketing, promotion, and sale of music on an
artist's site. It is designed to work as a freestanding API or integrated with
publishing and CMS systems like Wordpress or Drupal.

# Notes

Most of the code is currently found in /framework/local/php/ and centers around a
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

# License

CashMusic DIY is (c) 2010 CASH Music, licensed under a AGPL license:
<http://www.gnu.org/licenses/agpl-3.0.html>
