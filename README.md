# CASH Music Platform

The CASH Music Platform is very much still a work in progress in its pre-release
lifecycle.

The DIY version of the CASH platform is a locally-installable API intended to
work in a request/response model similar to a REST implementation and handle
complex functionality around the marketing, promotion, and sale of music on an
artist's site. It is designed to work as a freestanding API or integrated with
publishing and CMS systems like Wordpress or Drupal.

## Requirements

One of the fundamental goals of CASH Music is the be as widely deployable as possible,
and hence, to have extremely minimal dependencies outside of what comes bundled. Current
requirements:

 * [PHP](http://php.net) 5.2.7
 * PDO
 * MySQL or SQLite. Other databases could be supported if needed.
 * mod_rewrite (for admin)
 * fopen wrappers OR cURL (either, for fetching external feeds)

## (Really) Quick Start
To get your hands on a working instance as quickly as possible, fork/clone this 
repo and once it's local run /installers/php/dev_installer.php from the command 
line ("php path/to/DIY//installers/php/dev_installer.php") — choose the SQLite 
install option and supply an email for the admin login. Next just point Apache or 
MAMP at the main repo directory and view http://localhost/ in a browser.

## Notes

Most of the code is currently found in framework/php/ and centers around a
single-include workflow, which means the only thing you need to do to add CM to
your site/page is include this single file:

    // this loads CASH Music DIY
    $CASHMUSIC = "/home/billybob/cashmusic";
    require_once("$CASHMUSIC/framework/php/cashmusic.php");

where the $CASHMUSIC variable is the directory where you installed CASH Music
on your server.

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

## Getting the CASH Music codebase

To hack on CASH Music DIY, first grab the git repo:

    git clone git://github.com/cashmusic/DIY.git

If you are behind a pesky firewall, you might need to use https, but only do
this if you must, since it is much slower and uses up more bandwidth:

    git clone https://leto@github.com/cashmusic/DIY.git

## Running CASHMusic Tests

Read the test suite [https://github.com/cashmusic/DIY/blob/master/tests/README.md](README)

## CASHMusic Branches + Tags

Each week, we do a developer release on Thursday. The developer releases are tagged
with names in the form of 'dev_release_N' and are cut from the 'latest_stable' branch.
For example, [this](https://github.com/cashmusic/DIY/commits/dev_release_1) is our first
dev release tag.

The 'master' branch is the tip of development, where new commits and pull requests get
merged into, and will be more unstable and possibly broken for short periods of time.
The 'latest_stable' branch will always point to the most recent developer release.

## Contributing to CASH Music

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

    # replace USERNAME with your github username (or just paste the URL you just copied above)
    git clone git@github.com:USERNAME/DIY.git
    cd DIY

    # change to the latest_stable branch
    git checkout latest_stable

    # create a new topic branch
    git checkout -b my_cool_feature_branch

At this point, you actually hack on code, fix some docs, make something better, whatever. When
you get to a stopping point, add the files that you have changed or added:

    git add new_file.php changed_file.php

And then commit with a useful commit message:

    git commit -m "I promise that awesome new feature X will not set your dog on fire"

Commit early and often. It is like brushing your teeth: You can't do it too much. When you are
ready to send your changes up to your fork on the Github mothership:

    git push origin my_cool_feature_branch

Now you will be able to get feedback on your code, submit pull requests and generally bask in the
glory of social coding.

## Continuous Integration

We use something called [Jitterbug](http://jitterbug.pl) to run our tests every
time someone pushes to the CASH Music DIY Github repo. You can see the results
of each test run [here](http://dev.cashmusic.org:3000/project/DIY) .

## Using SQLite

PHP may not have SQLite support. On Debian-based systems, you can install it with

    sudo apt-get install php5-sqlite

### Generatign a SQLite Database

In the root of DIY.git type:

    make sqlite_db

and a file called "cash.db" will be created. This will likely become part of the web interface Real Soon Now.

### Regenerating the SQLite Schema

Currently, we check in the [MySQL schema](https://github.com/cashmusic/DIY/blob/master/framework/php/settings/sql/cashmusic_db.sql)
to DIY.git each time it changes. To update the [SQLite version of the schema](https://github.com/cashmusic/DIY/blob/master/framework/php/settings/sql/cashmusic_db_sqlite.sql),
you need to use a utility to convert it.

You will need to install the [CPAN](http://cpan.org) module [SQL::Translator](https://metacpan.org/module/SQL::Translator), then run this from the root of the CM repo:

    make sqlite_schema

If the SQLite schema is up to date, ```git status``` will not show anything as changed. If you *do* see changes, then commit the change and push it.

An easy way to install it on Debian-based systems is:

    sudo apt-get install sqlfairy

## License

CashMusic DIY is (c) 2010 CASH Music, licensed under a AGPL license:
<http://www.gnu.org/licenses/agpl-3.0.html>
