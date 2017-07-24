# Chadicus\Slim\OAuth2\Middleware

[![Build Status](https://travis-ci.org/chadicus/slim-oauth2-middleware.svg?branch=master)](https://travis-ci.org/chadicus/slim-oauth2-middleware)
[![Code Quality](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-middleware/?branch=master)
[![Code Coverage](https://coveralls.io/repos/github/chadicus/slim-oauth2-middleware/badge.svg?branch=master)](https://coveralls.io/github/chadicus/slim-oauth2-middleware?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/55b9075e653762001a0012b3/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55b9075e653762001a0012b3)
[![Reference Status](https://www.versioneye.com/php/chadicus:slim-oauth2-middleware/reference_badge.svg?style=flat)](https://www.versioneye.com/php/chadicus:slim-oauth2-middleware/references)

[![Latest Stable Version](https://poser.pugx.org/chadicus/slim-oauth2-middleware/v/stable)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![Latest Unstable Version](https://poser.pugx.org/chadicus/slim-oauth2-middleware/v/unstable)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![License](https://poser.pugx.org/chadicus/slim-oauth2-middleware/license)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)

[![Total Downloads](https://poser.pugx.org/chadicus/slim-oauth2-middleware/downloads)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![Daily Downloads](https://poser.pugx.org/chadicus/slim-oauth2-middleware/d/daily)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)
[![Monthly Downloads](https://poser.pugx.org/chadicus/slim-oauth2-middleware/d/monthly)](https://packagist.org/packages/chadicus/slim-oauth2-middleware)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://pholiophp.org/chadicus/slim-oauth2-middleware)

Middleware for using [OAuth2 Server](http://bshaffer.github.io/oauth2-server-php-docs/) within a [Slim 3 Framework](http://www.slimframework.com/) API

## Requirements

Chadicus\Slim\OAuth2\Middleware requires PHP 5.6 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`chadicus/slim-oauth2-middleware` to your project's `composer.json` file such as:

```sh
composer require chadicus/slim-oauth2-middleware
```

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/chadicus/slim-oauth2-middleware/pulls)
 * [Issues](https://github.com/chadicus/slim-oauth2-middleware/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
./composer install
./vendor/bin/phpunit
```

##Example Usage

Simple example for using the authorization middleware.

```php
use Chadicus\Slim\OAuth2\Middleware;
use OAuth2;
use OAuth2\Storage;
use OAuth2\GrantType;
use Slim;

//set up storage for oauth2 server
$storage = new Storage\Memory(
    [
        'client_credentials' => [
            'testClientId' => [
                'client_id' => 'chadicus-app',
                'client_secret' => 'password',
            ],
        ],
    ]
);

// create the oauth2 server
$server = new OAuth2\Server(
    $storage,
    [
        'access_lifetime' => 3600,
    ],
    [
        new GrantType\ClientCredentials($storage),
    ]
);

//create the basic app
$app = new Slim\App();

// create the authorization middlware
$authMiddleware = new Middleware\Authorization($server, $app->getContainer());

//Assumes token endpoints available for creating access tokens

$app->get('foos', function ($request, $response, $args) {
    //return all foos, no scope required
})->add($authMiddleware);

$getRouteCallback = function ($request, $response, $id) {
    //return details for a foo, requires superUser scope OR basicUser with canViewFoos scope
};

$app->get('foos/id', $getRouteCallback)->add($authMiddleware->withRequiredScope(['superUser', ['basicUser', 'canViewFoos']]));

$postRouteCallback = function ($request, $response, $args) {
    //Create a new foo, requires superUser scope
};

$app->post('foos', $postRouteCallback)->add($authMiddleware->withRequiredScope(['superUser']));

$app->run();
```
