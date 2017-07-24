# Slim Token Authentication Example

This is a simple example of how implements token authentication with Slim application.
See complete documentation on [Slim Token Authentication](https://github.com/dyorg/slim-token-authentication).

## Installing dependencies

```bash
composer update
```

## Making authentication via header

On your prompt:

```bash
$ curl -i http://localhost/slim-token-authentication/example/restrict -H "Authorization: Bearer usertokensecret"
```

## Making authentication via query paramater

On your prompt:

```bash
$ curl -i http://localhost/slim-token-authentication/example/restrict?authorization=usertokensecret
```

Instead you can try authentication with parameter via your browser:

```bash
http://localhost/slim-token-authentication/example/restrict?authorization=usertokensecret
```

## Responses

On success should return something like:

```bash
HTTP/1.1 200 OK
Date: Wed, 15 Jun 2016 21:26:09 GMT
Server: Apache/2.4.12 (Win64) OpenSSL/1.0.1m PHP/5.6.9
X-Powered-By: PHP/5.6.9
Content-Length: 59
Content-Type: text/html; charset=UTF-8

{"msg":"It's a restrict area. Token authentication works!"}
```

With wrong token should return something like:

```bash
HTTP/1.1 401 Unauthorized
Date: Wed, 15 Jun 2016 21:28:26 GMT
Server: Apache/2.4.12 (Win64) OpenSSL/1.0.1m PHP/5.6.9
X-Powered-By: PHP/5.6.9
Content-Length: 52
Content-Type: application/json;charset=utf-8

{"message":"Invalid Token","token":"usertokenwrong"}
```

