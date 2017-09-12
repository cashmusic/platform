[![Funded by The Shuttleworth Foundation](https://cloud.githubusercontent.com/assets/368640/11352290/6d23f2fe-91f0-11e5-9104-53eab0a36abe.jpg)](https://www.shuttleworthfoundation.org/)

# CASH Music Platform

The CASH Music platform gives everyone access to tools that let them manage, 
promote, and sell their music online — all owned and controlled themselves.

The platform can be used as a PHP library, integrated into popular CMS systems, 
or standalone with the included admin app. This repo contains the core framework, 
installers, an admin webapp, APIs, demos, and a full suite of tests.

[![Build Status](https://secure.travis-ci.org/cashmusic/platform.svg)](http://travis-ci.org/cashmusic/platform)

 
![Dev site included in repo](https://b6febe3773eb5c5bc449-6d885a724441c07ff9b675222419a9d2.ssl.cf2.rackcdn.com/special/docs/dev_screenshot.jpg)


## Requirements

One of our goals is for this to run in as many places as possible, so we've worked 
hard to keep the requirements minimal:

 * PHP 5.4+
 * PDO (a default) and MySQL OR SQLite 
 * mod_rewrite (for admin app)
 * fopen wrappers OR cURL 

## More

For more about installation, working with the platform, check out [the wiki](https://github.com/cashmusic/platform/wiki).

## Submitting a pull request

We the 'master' branch release-ready at all times, so we ask all contributors to [TEST](https://github.com/cashmusic/platform/blob/master/tests/README.md) your code before submitting a pull request. Please 
create a descriptively named branch off your repo and give as many details in your pull request as possible.

We view pull requests as conversations. Submit a pull request early if you're working on something and
have questions. We'll work with you to get it where it needs to be for a merge.

## Copyright & License

The CASH Music platform is (c) 2010-2014 CASH Music, licensed under an 
[AGPL license](http://www.gnu.org/licenses/agpl-3.0.html) (Some components, like
the core framework, are licensed LGPL. See LICENSE docs for more.)
