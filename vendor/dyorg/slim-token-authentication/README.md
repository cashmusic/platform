# Slim Token Authentication

This is a Token Authentication Middleware for Slim 3.0+. 
This middleware was designed to maintain easy to implement token authentication with custom authenticator.  

## Installing

Get last version with [Composer](http://getcomposer.org "Composer").

```bash
composer require dyorg/slim-token-authentication
```

## Getting authentication

Start creating a `authenticator` function, this function will make the token validation of your application.
When you create a new instance of `TokenAuthentication` you must pass a array with configuration options. 
You need setting authenticator and path options to authentication start work.

```php
$authenticator = function($request, TokenAuthentication $tokenAuth){

    # Search for token on header, parameter, cookie or attribute
    $token = $tokenAuth->findToken($request);
    
    # Your method to make token validation
    $user = User::auth_token($token);
    
    # If occured ok authentication continue to route
    # before end you can storage the user informations or whatever
    ...
    
};

$app = new App();

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator
]));
```

### Find Token

This middleware contains the method `findToken()`, you can access it from your authenticator method through the second param (`TokenAuthentication` instance). 
This method is able to search for authentication token on header, parameter, cookie or attribute.
You can configure it through options settings.

## Configuration Options

### Path

By default none route require authentication. 
You must set one or more routes to be restrict by authentication, setting it on `path` option.
 
```php
...

$app = new App();

$app->add(new TokenAuthentication([
    'path' => '/api', /* or ['/api', '/docs'] */
    'authenticator' => $authenticator
]));
```

### Passthrough

You can configure which routes do not require authentication, setting it on `passthrough` option.

```php
...

$app = new App();

$app->add(new TokenAuthentication([
    'path' => '/api',
    'passthrough' => '/api/auth', /* or ['/api/auth', '/api/test'] */
    'authenticator' => $authenticator
]));
```

### Header

By default middleware tries to find token from `Authorization` header. You can change header name using `header` option.
Is expected in Authorization header the value format as `Bearer <token>`, it is matched using a regular expression. 
If you want to work without token type or with other token type, like `Basic <token>`, 
you can change the regular expression pattern setting it on `regex` option.
You can disabled authentication via header setting `header` option as null.

```php
...

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator,
    'header' => 'Token-Authorization-X',
    'regex' => '/Basic\s+(.*)$/i', /* for without token type can use /\s+(.*)$/i */
]));
```

### Parameter

If token is not fount on header, middleware tries to find `authorization` query parameter. 
You can change parameter name using `parameter` option. 
You can disabled authentication via parameter setting `parameter` option as null.

```php
...

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator,
    'parameter' => 'token'
]));
```

### Cookie

As a last resort, middleware tries to find `authorization` cookie. 
You can change cookie name using `cookie` option. 
You can disabled authentication via cookie setting `cookie` option as null.

```php
...

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator,
    'cookie' => 'token'
]));
```

### Attribute

By default, middleware not tries to find token `authorization` attribute of route.
To enable authentication via attribute you must setting a name for attribute on `attribute` option.

```php
...

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator,
    'attribute' => 'authorization'
]));
```

### Error

By default on ocurred a fail on authentication, is sent a response on json format with a message (`Invalid Token` or `Not found Token`) and with the token (if found), with status `401 Unauthorized`.
You can custom it by setting a callable function on `error` option.

```php
...

$error = function($request, $response, TokenAuthentication $tokenAuth) {
    $output = [];
    $output['error'] = [
        'msg' => $tokenAuth->getResponseMessage(),
        'token' => $tokenAuth->getResponseToken(),
        'status' => 401,
        'error' => true
    ];
    return $response->withJson($output, 401);
}

$app = new App();

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator
    'error' => $error
]));
```

This error function is called when `TokenAuthentication` catch an throwable class that implements `UnauthorizedExceptionInterface`.

### Secure

Tokens are essentially passwords. You should treat them as such and you should always use HTTPS. 
If the middleware detects insecure usage over HTTP it will return unathorized with a message `Required HTTPS for token authentication`. 
This rule is relaxed for requests on localhost. To allow insecure usage you must enable it manually by setting `secure` to false.

```php
...

$app = new App();

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator,
    'secure' => false
]));
```

Alternatively you can list your development host to have `relaxed` security.

```php
...

$app->add(new TokenAuthentication([
    'path' => '/api',
    'authenticator' => $authenticator,
    'secure' => true,
    'relaxed' => ['localhost', 'your-app.dev']
]));
```

## Example

See how use it on [/example](https://github.com/dyorg/slim-token-authentication/tree/master/example)
