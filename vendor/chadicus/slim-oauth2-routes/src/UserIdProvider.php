<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Psr\Http\Message\ServerRequestInterface;

/**
 * UserId Provider which extracts the user_id from the request GET parameters.
 */
final class UserIdProvider implements UserIdProviderInterface
{
    /**
     * Extracts a user_id from the given HTTP request query params.
     *
     * @param ServerRequestInterface $request   The incoming HTTP request.
     * @param array                  $arguments Any route parameters associated with the request.
     *
     * @return string|null The user id if it exists, otherwise null
     */
    public function getUserId(ServerRequestInterface $request, array $arguments = [])
    {
         $queryParams = $request->getQueryParams();
         return array_key_exists('user_id', $queryParams) ? $queryParams['user_id'] : null;
    }
}
