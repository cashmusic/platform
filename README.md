# CASH Music Platform / DIY

The CASH Music Platform is very much still a work in progress in its pre-release
lifecycle.

The DIY version of the CASH platform is a locally-installable API intended to
work in a request/response model similar to a REST implementation and handle
complex functionality around the marketing, promotion, and sale of music on an
artist's site. It is designed to work as a freestanding API or integrated with
publishing and CMS systems like Wordpress or Drupal.

# Requirements

One of the fundamental goals of CASH Music is the be as widely deployable as possible,
and hence, to have extremely minimal dependencies outside of what comes bundled. Current
requirements:

 * [PHP](http://php.net) 5.2.3
 * MySQL (other databases may work, but have not been tested)

Plans are in the works to support SQLite.

# Notes

Most of the code is currently found in framework/php/ and centers around a
single-include workflow, which means the only thing you need to do to add CM to
your site/page is include this single file:

    // this loads CASH Music DIY
    require_once('framework/php/cashmusic.php');

The Seed.php does some basic housekeeping before firing up a CASHRequest
instance that parses an incoming request. That request is passed to the
appropriate Plant (kind of like a Factory) class which then figures out what to
do with the request and fires off any necessary Seed (kind of like a Worker)
classes. When done the Plant returns any information in a standard CASHResponse
format which is stored in a standardized session variable and in the original
CASHRequest.

So something like this:

    CASHRequest->Plant->(Seed(s) if needed)->CASHResponse

Long-term goal is to use standardized requests/responses to enable full action
chaining for new functionality.

# Getting the CASH Music codebase

To hack on CASH Music DIY, first grab the git repo:

    git clone git://github.com/cashmusic/DIY.git

If you are behind a pesky firewall, you might need to use https, but only do
this if you must, since it is much slower and uses up more bandwidth:

    git clone https://leto@github.com/cashmusic/DIY.git

# Running CASHMusic Tests

We have a test suite to make sure that things don't break when we add features
and fix bugs. Currently we syntax check all the PHP files and have a few basic
tests for creating the most basic CASHMusic DIY objects, but soon it will be a
beautiful butterfly.

To run the CASH Music PHP tests, run this command from the root directory of the
CASH Music git repo:

    cd DIY
    php tests/php/all.php

When the tests pass, you should see something like this at the end:

    OK
    Test cases run: 3/3, Passes: 43, Failures: 0, Exceptions: 0

Our PHP tests are written using [SimpleTest](http://www.simpletest.org/). Currently
we do not have any Javascript tests, but those are on the way.

# CASHMusic Branches + Tags

Each week, we do a developer release on Thursday. The developer releases are tagged
with names in the form of 'dev_release_N' and are cut from the 'latest_stable' branch.
For example, [this](https://github.com/cashmusic/DIY/commits/dev_release_1) is our first
dev release tag.

The 'master' branch is the tip of development, where new commits and pull requests get
merged into, and will be more unstable and possibly broken for short periods of time.
The 'latest_stable' branch will always point to the most recent developer release.

# Contributing to CASH Music

We highly encourage *everybody* to scratch their itch and contribute back to CASH Music.

We prefer [Github](https://github.com) [pull requests](http://help.github.com/send-pull-requests/),
but we will take contributions any way we can get them. If you need to etch a
patch on a grain of rice, please send a magnifying glass.

We assume that you already have a free [Github account](https://github.com/signup/free). If you don't already,
do that now. We promise, it is the bees knees.

First, you will want to create your "fork" of CASH Music DIY. To do this, go to
the [CASH Music DIY Github project](https://github.com/cashmusic/DIY) and click
on the grey "Fork" button on the top right. You should see a note about
"Hardcore Forking Action" happening (teehee) and then the page will reload so
you can look at your spiffy new fork!

Next, click the little button next to "Read+Write access" just above the the
box describing the latest commit.  That will copy the URL of your fork to your
clipboard.

    git clone git@github.com:USERNAME/DIY.git
    cd DIY
    git checkout latest_stable
    git checkout -b my_cool_feature_branch

TO BE FINISHED

# Continuous Integration

We use something called [Jitterbug](http://jitterbug.pl) to run our tests every
time someone pushes to the CASH Music DIY Github repo. You can see the results
of each test run at http://dev.cashmusic.org:3000/project/DIY .

# License

CashMusic DIY is (c) 2010 CASH Music, licensed under a AGPL license:
<http://www.gnu.org/licenses/agpl-3.0.html>
