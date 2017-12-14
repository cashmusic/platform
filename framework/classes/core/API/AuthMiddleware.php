<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 7/25/17
 * Time: 3:20 PM
 */

namespace CASHMusic\Core\API;

use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use CASHMusic\Core\CASHSystem;

class AuthMiddleware extends ResourceServerMiddleware
{

    public function __construct(AccessTokenRepository $accessTokenRepository) {
        $server = new ResourceServer(
            $accessTokenRepository, CASH_PLATFORM_ROOT . "/settings/keys/public.key"
        );

        parent::__construct($server);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($request->getAttribute('auth_required') === true) {
            CASHSystem::errorLog("this no");
            return parent::__invoke($request, $response, $next);
            // else set authed true
        }

        return $next($request, $response);
    }

}