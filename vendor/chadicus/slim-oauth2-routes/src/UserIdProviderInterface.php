<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines the interface for extracting a user_id from an incoming http request.
 */
interface UserIdProviderInterface
{
    /**
     * Extracts a user_id from the given HTTP request and route parameters.
     *
     * @param ServerRequestInterface $request   The incoming HTTP request.
     * @param array                  $arguments Any route parameters associated with the request.
     *
     * @return string|null The user id if it exists, otherwise null.
     */
    public function getUserId(ServerRequestInterface $request, array $arguments = []);
}
