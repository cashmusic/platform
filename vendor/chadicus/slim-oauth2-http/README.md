# Chadicus\Slim\OAuth2\Http

[![Build Status](https://travis-ci.org/chadicus/slim-oauth2-http.svg?branch=master)](https://travis-ci.org/chadicus/slim-oauth2-http)
[![Code Quality](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-http/?branch=master)
[![Code Coverage](https://coveralls.io/repos/github/chadicus/slim-oauth2-http/badge.svg?branch=master)](https://coveralls.io/github/chadicus/slim-oauth2-http?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/584459fdb1c38c0aa1cd471b/badge.svg?style=flat)](https://www.versioneye.com/user/projects/584459fdb1c38c0aa1cd471b)
[![Reference Status](https://www.versioneye.com/php/chadicus:slim-oauth2-http/reference_badge.svg?style=flat)](https://www.versioneye.com/php/chadicus:slim-oauth2-http/references)

[![Latest Stable Version](https://poser.pugx.org/chadicus/slim-oauth2-http/v/stable)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![Latest Unstable Version](https://poser.pugx.org/chadicus/slim-oauth2-http/v/unstable)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![License](https://poser.pugx.org/chadicus/slim-oauth2-http/license)](https://packagist.org/packages/chadicus/slim-oauth2-http)

[![Total Downloads](https://poser.pugx.org/chadicus/slim-oauth2-http/downloads)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![Daily Downloads](https://poser.pugx.org/chadicus/slim-oauth2-http/d/daily)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![Monthly Downloads](https://poser.pugx.org/chadicus/slim-oauth2-http/d/monthly)](https://packagist.org/packages/chadicus/slim-oauth2-http)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://pholiophp.org/chadicus/slim-oauth2-http)

Static utilitiy classes to bridge [PSR-7](http://www.php-fig.org/psr/psr-7/) http messages to [OAuth2 Server](http://bshaffer.github.io/oauth2-server-php-docs/) requests and responses. While this libray is entended for use with [Slim 3](http://www.slimframework.com/), it should work with any PSR-7 compatible framework.

## Requirements

Chadicus\Slim\OAuth2\Http requires PHP 5.6 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on `chadicus/slim-oauth2-http` to your project's `composer.json` file such as:

```sh
composer require chadicus/slim-oauth2-http
```

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/chadicus/slim-oauth2-http/pulls)
 * [Issues](https://github.com/chadicus/slim-oauth2-http/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
composer install
./vendor/bin/phpunit
./vendor/bin/phpcs --standard=./vendor/chadicus/coding-standard/Chadicus -n src
```

##Available Operations

###Convert a PSR-7 request to an OAuth2 request
```php
use Chadicus\Slim\OAuth2\Http\RequestBridge;

$oauth2Request = RequestBridge::toOAuth2($psrRequest);
```

###Convert an OAuth2 response to a PSR-7 response.
```php
use Chadicus\Slim\OAuth2\Http\ResponseBridge;

$psr7Response = ResponseBridge::fromOAuth2($oauth2Request);
```

##Example Integeration

###Simple route for creating a new oauth2 access token
```php
use Chadicus\Slim\OAuth2\Http\RequestBridge;
use Chadicus\Slim\OAuth2\Http\ResponseBridge;
use OAuth2;
use OAuth2\GrantType;
use OAuth2\Storage;
use Slim;

$storage = new Storage\Memory(
    [
        'client_credentials' => [
            'testClientId' => [
                'client_id' => 'testClientId',
                'client_secret' => 'testClientSecret',
            ],
        ],
    ]
);

$server = new OAuth2\Server(
    $storage,
    [
        'access_lifetime' => 3600,
    ],
    [
        new GrantType\ClientCredentials($storage),
    ]
);

$app = new Slim\App();

$app->post('/token', function ($psrRequest, $psrResponse, array $args) use ($app, $server) {
    //create an \OAuth2\Request from the current \Slim\Http\Request Object
    $oauth2Request = RequestBridge::toOAuth2($psrRequest);

    //Allow the oauth2 server instance to handle the oauth2 request
    $oauth2Response = $server->handleTokenRequest($oauth2Request),

    //Map the oauth2 response into the slim response
    return ResponseBridge::fromOAuth2($oauth2Response);
});

```
