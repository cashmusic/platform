<?php

/*
 * This file is part of Slim Token Authentication Middleware
 *
 * Copyright (c) 2016 Dyorg Almeida
 *
 * Licensed under the MIT license
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Slim\Middleware;

use Slim\Middleware\TokenAuthentication\TokenSearch;
use Slim\Middleware\TokenAuthentication\UnauthorizedExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TokenAuthentication
{
    private $options = [
        'secure' => true,
        'relaxed' => ['localhost', '127.0.0.1'],
        'path' => null,
        'passthrough' => null,
        'authenticator' => null,
        'error' => null,
        'header' => 'Authorization',
        'regex' => '/Bearer\s+(.*)$/i',
        'parameter' => 'authorization',
        'cookie' => 'authorization',
        'argument' => 'authorization'
    ];

    private $response = [];

    public function __construct(array $options = [])
    {
        /** Rewrite options */
        $this->fill($options);
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();

        /** If rules say we should not authenticate call next and return. */
        if (false === $this->shouldAuthenticate($request)) {
            return $next($request, $response);
        }

        /** HTTP allowed only if secure is false or server is in relaxed array. */
        if ("https" !== $scheme && true === $this->options["secure"]) {
            if (!in_array($host, $this->options["relaxed"])) {
                return $response->withJson(['message' => 'Required HTTPS for token authentication.'], 401);
            }
        }

        /** Call custom authenticator function */
        if (empty($this->options['authenticator']))
            throw new \RuntimeException('authenticator option has not been set or it is not callable.');

        try {

            if ($this->options['authenticator']($request, $this) === false) {
                return $this->error($request, $response);
            }

            return $next($request, $response);

        } catch (UnauthorizedExceptionInterface $e) {
            $this->setResponseMessage($e->getMessage());
            return $this->error($request, $response);
        }
    }

    private function fill($options = array())
    {
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
    }

    public function shouldAuthenticate(Request $request)
    {
        $uri = $request->getUri()->getPath();
        $uri = '/' . trim($uri, '/');

        /** If request path is matches passthrough should not authenticate. */
        foreach ((array)$this->options["passthrough"] as $passthrough) {
            $passthrough = rtrim($passthrough, "/");
            if (preg_match("@^{$passthrough}(/.*)?$@", $uri)) {
                return false;
            }
        }

        /** Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }

        return false;
    }

    public function error(Request $request, Response $response)
    {
        /** If exists a custom error function callable, ignore remaining code */
        if (!empty($this->options['error'])) {
            
            $custom_error_response = $this->options['error']($request, $response, $this);

            if ($custom_error_response instanceof Response) {
               return $custom_error_response;
            } else {
                throw new \Exception("The error function must return an object of class Response.");
            }
        }

        if ($this->getResponseMessage())
            $res['message'] = $this->getResponseMessage();
        else
            $res['message'] = 'Invalid authentication token';

        if ($this->getResponseToken()) {
            $res['token'] = $this->getResponseToken();
        }

        return $response->withJson($res, 401, JSON_PRETTY_PRINT);
    }

    public function findToken(Request $request)
    {
        $tokenSearch = new TokenSearch([
            'header' => $this->options['header'],
            'regex' => $this->options['regex'],
            'parameter' => $this->options['parameter'],
            'cookie' => $this->options['cookie'],
            'argument' => $this->options['argument']
        ]);

        $token = $tokenSearch($request);
        $request->withAttribute('authorization', $token);
        $this->setResponseToken($token);

        return $token;
    }

    public function setResponseMessage($message)
    {
        $this->response['message'] = $message;
        return $this;
    }

    public function getResponseMessage()
    {
        return isset($this->response['message']) ? $this->response['message'] : null;
    }

    public function setResponseToken($token)
    {
        $this->response['token'] = $token;
        return $this;
    }

    public function getResponseToken()
    {
        return isset($this->response['token']) ? $this->response['token'] : null;
    }

    /** Use to set multiples messages and after get on custom error function */
    public function setResponseArray(array $args = [])
    {
        foreach ($args as $name => $text) {
            return $this->response[$name] = $text;
        }
        return $this;
    }

    public function getResponseByName($name)
    {
        return isset($this->response[$name]) ? $this->response[$name] : null;
    }

    public function setSecure($secure)
    {
        $this->options['secure'] = (bool) $secure;
        return $this;
    }

    public function getSecure()
    {
        return $this->options['secure'];
    }

    public function setRelaxed($relaxed)
    {
        $this->options['relaxed'] = (array) $relaxed;
        return $this;
    }

    public function getRelaxed()
    {
        return $this->options['relaxed'];
    }

    public function setPath($path)
    {
        $this->options['path'] = (array) $path;
        return $this;
    }

    public function getPath()
    {
        return $this->options['path'];
    }

    public function setPassthrough($passthrough)
    {
        $this->options['passthrough'] = (array) $passthrough;
        return $this;
    }

    public function getPassthrough()
    {
        return $this->options['passthrough'];
    }

    public function setError(Callable $error)
    {
        $this->options['error'] = $error;
        return $this;
    }

    public function getError()
    {
        return $this->options['error'];
    }

    public function setAuthenticator(Callable $authenticator)
    {
        $this->options['authenticator'] = $authenticator;
        return $this;
    }

    public function getAuthenticator()
    {
        return $this->options['authenticator'];
    }

    public function setHeader($header)
    {
        $this->options['header'] = $header;
        return $this;
    }

    public function getHeader()
    {
        return $this->options['header'];
    }

    public function setRegex($regex)
    {
        $this->options['regex'] = $regex;
        return $this;
    }

    public function getRegex()
    {
        return $this->options['regex'];
    }

    public function setParameter($parameter)
    {
        $this->options['parameter'] = $parameter;
        return $this;
    }

    public function getParameter()
    {
        return $this->options['parameter'];
    }

    public function setArgument($argument)
    {
        $this->options['argument'] = $argument;
        return $this;
    }

    public function getArgument()
    {
        return $this->options['argument'];
    }

    public function setCookie($cookie)
    {
        $this->options['cookie'] = $cookie;
        return $this;
    }

    public function getCookie()
    {
        return $this->options['cookie'];
    }
}