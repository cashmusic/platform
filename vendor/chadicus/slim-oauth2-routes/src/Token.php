<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Chadicus\Slim\OAuth2\Http\RequestBridge;
use Chadicus\Slim\OAuth2\Http\ResponseBridge;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use OAuth2;

/**
 * Slim route for /token endpoint.
 */
final class Token implements RouteCallbackInterface
{
    const ROUTE = '/token';

    /**
     * The OAuth2 server instance.
     *
     * @var OAuth2\Server
     */
    private $server;

    /**
     * Create a new instance of the Token route.
     *
     * @param OAuth2\Server $server The oauth2 server imstance.
     */
    public function __construct(OAuth2\Server $server)
    {
        $this->server = $server;
    }

    /**
     * Invoke this route callback.
     *
     * @param ServerRequestInterface $request   Represents the current HTTP request.
     * @param ResponseInterface      $response  Represents the current HTTP response.
     * @param array                  $arguments Values for the current route’s named placeholders.
     *
     * @return RequestInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $arguments = [])
    {
        $response = ResponseBridge::fromOAuth2(
            $this->server->handleTokenRequest(RequestBridge::toOAuth2($request))
        );

        if ($response->hasHeader('Content-Type')) {
            return $response;
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
