![CASH Music Self-Install Platform](https://cashmusic.s3.amazonaws.com/permalink/images/readme.jpg)

The CASH Music platform gives everyone access to tools that let them manage, 
promote, and sell their music online — all owned and controlled themselves.

The whole thing is designed to work as a freestanding API or integrated with
publishing and CMS systems like Wordpress or Drupal. This repo contains the core 
framework, installers, an admin webapp, APIs, demos, and a full suite of tests.

[![Build Status](https://secure.travis-ci.org/cashmusic/platform.png)](http://travis-ci.org/cashmusic/platform)

## Requirements

One of our goals is for this to run in as many places as possible, so we've worked 
hard to keep the requirements as minimal as possible:

 * PHP 5.2.7+
 * PDO and MySQL OR SQLite
 * mod_rewrite (for admin)
 * fopen wrappers OR cURL 

## Installation 

**Coders:** Just fork/clone this repo, cd into the new /platform folder, and run: 

```php
make install
````

choosing SQLite will get you up and running fastest. Next just point Apache or MAMP 
at the /platform directory and view http://localhost/ in a browser.

  
**Non-coders:** We've included a web installer specifically meant for installing over 
FTP to a web host. Just download it [here](https://github.com/cashmusic/platform/downloads), 
create a new folder for it on your web host, upload, and visit in a web browser. The 
installer will do the rest. 

## Working with the platform

For musicians, the primary workflow is just logging into the admin app, managing their 
world, and defining new elements for public-facing promos. Publishing is always a 
single line copy+paste away, no real coding needed. 

The best way to currently embed an element is with a line of PHP, but we're also 
working on a JavaScript embed and WordPress plugin. Our PHP includes look like: 

```php
<?php 
	include('/path/to/cash/framework/php/cashmusic.php'); // Initialize CASH Music ?>
	CASHSystem::embedElement(1); // CASH element 
?>
``` 

For developers looking to build custom functionality we've built a request/response 
process as the primary interface to the PHP framework. It mimics a REST-style API 
and standardizes calls and responses across the methods...so the PHP interaction 
will be nearly identical to future REST or verbose interactions. 

The PHP Request/Response looks like this: 

```php
<?php 
	$sample_request = new CASHRequest(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'getevent',
			'id' => 43
		)
	);

	$sample_request->response:
	{
		"status_code":404, // http-style status code
		"status_uid":"calendar_getevent_404", // uid of the response (request + status)
		"status_message":"Not Found", // http-style status message
		"contextual_message":"Event not found", // more specific error message
		"request_type":"calendar", // echo of the request type
		"action":"getevent", // echo of the request action
		"payload":false, // contents of the response, or false if error
		"api_version":2, // version number
		"timestamp":1335416260 // request timestamp
	}
?>
```

## Testing

More about our testing habits in the test suite [README](https://github.com/cashmusic/platform/blob/master/tests/README.md)

## Admin App

We've got a [README](https://github.com/cashmusic/platform/blob/master/interfaces/php/admin/README.md) for that dude too.

## CASH Music Branches + Tags

We try to keep the 'master' branch release-ready at all times, but it is the first 
place new changes are merged into — be aware that it will break from time to time.
Contributors are asked to fork, create a branch, and submit pull requests to 'master.'

The 'latest_stable' branch will always point to the most recent public release.

## Copyright & License

The CASH Music platform is (c) 2010-2012 CASH Music, licensed under the 
[AGPL license](http://www.gnu.org/licenses/agpl-3.0.html)
