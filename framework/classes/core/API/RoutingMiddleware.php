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

            //list($restful_routes, $soap_routes) = self::getRoutingTables($plant);

            if ($route_response = self::validateRequestedRoute($plant, $noun, $method, false)) {
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
        }

        $response = $next($request, $response);

        return $response;
    }

    /**
     * @param $plant
     */
    public static function getPlantDirectory($plant)
    {
        return CASH_PLATFORM_ROOT."/classes/plants/".str_replace("Plant", "", $plant)."/";
    }

    public static function getRoutingTables($plant) {
        try {

            $routing_table = CASHSystem::getFileContents(
                self::getPlantDirectory($plant) ."routing.json", true
            );

            $routing_to_array = json_decode($routing_table, true);
            $restful_routes = $routing_to_array['restfulnouns'];
            $soap_routes = $routing_to_array['requestactions'];

            return [$restful_routes, $soap_routes];
        } catch (\Exception $e) {
            CASHSystem::errorLog($e->getMessage());
            return false;
        }
    }

    public static function validateRequestedRoute($plant, $noun, $method, $auth=false) {
        list($restful_routes, $soap_routes) = self::getRoutingTables($plant);

        if ($restful_route = (isset($restful_routes[$noun])) ? $restful_routes[$noun] : false) {
            // check method + ACL + $auth
            if (isset($restful_route['verbs'][$method])) {
                $verb = $restful_route['verbs'][$method];
                CASHSystem::errorLog("REST");

                if (isset($verb['authrequired'], $verb['plantfunction'], $verb['description'])) {
                    return $verb;
                } else {
                    return false;
                }
            }

            return false;
        }

        if ($soap_route = (isset($soap_routes[$noun])) ? $soap_routes[$noun] : false) {
            // check method ACL + $auth
            if (in_array('api_public', $soap_route['security']) ||
                in_array('api_key', $soap_route['security'])) {
                CASHSystem::errorLog("SOAP");
                // do request
                return [
                    'description' => $soap_route['description'],
                    'plantfunction' => $soap_route['plantfunction'],
                    'authrequired' => (in_array('api_public', $soap_route['security'])) ? true : false
                ];

            } else {
                return false;
            }
        }

        return false;
    }
}