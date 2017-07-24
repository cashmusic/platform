<?php

namespace CASHMusic\Core\API;

use CASHMusic\Core\CASHAPI;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

class RoutingMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $attributes = $request->getAttribute('routeInfo')[2];

        $plants = CASHRequest::buildPlantArray();

        if (array_key_exists($attributes['plant'], $plants)) {
            $plant = $plants[$attributes['plant']];
            $noun = $attributes['noun'];

            // let's convert PUT to POST
            $method = ($request->getMethod() == "PUT") ? "POST" : $request->getMethod();
            $headers = $request->getHeaders();

            list($restful_routes, $soap_routes) = CASHAPI::getRoutingTables($plant);

            if ($route_response = CASHAPI::validateRequestedRoute($plant, $noun, $method, false)) {
                // parse response
                $auth_required = false;

                if ($route_response['authrequired']) {
                    // auth check
                    $auth_required = true;
                }

                $request = $request->withAttribute('auth_required', $auth_required);
                $request = $request->withAttribute('route_settings', $route_response);
            } else {
                $request = $request->withAttribute('route_settings', false);
            }
                //$api->isAuthenticatedRequest($request);

/*            if ($response = $api->validateRequestedRoute($plant, $noun, $method, false)) {
                // parse response
                echo "wee";
            } else {

            }*/

        }


        $response = $next($request, $response);

        return $response;
    }
}