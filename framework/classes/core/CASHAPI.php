<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 7/23/17
 * Time: 1:27 PM
 */

namespace CASHMusic\Core;

use CASHMusic\Core\API\RoutingMiddleware;
use Slim\App as Slim;
use Slim\Http;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class CASHAPI
{

    public function __construct()
    {
        CASHSystem::startUp(false);

/*        $cash_db_settings = CASHSystem::getSystemSettings();

        $cashdba = new CASHDBA(
            $cash_db_settings['hostname'],
            $cash_db_settings['username'],
            $cash_db_settings['password'],
            $cash_db_settings['database'],
            $cash_db_settings['driver']
        );

        $cashdba->connect();

        $storage = new Storage\Pdo($cashdba->db);

        $server = new \OAuth2\Server(
            $storage,
            [
                'access_lifetime' => 3600,
            ],
            [
                new GrantType\ClientCredentials($storage),
                new GrantType\AuthorizationCode($storage),
            ]
        );*/

        $api = new Slim([
            'settings' => [
                'determineRouteBeforeAppMiddleware' => true,
            ]
        ]);

        $api->any('/{plant}/{noun}', function ($request, $response, $args) use (&$authorization) {
            if ($route = $request->getAttribute('route_settings')) {
                $request_params = $request->getQueryParams();
                if (isset($request_params['p'])) unset($request_params['p']);

                $logged_in = false;
                if ($request->getAttribute('auth_required')) {
                    // if !authed return 403

                    // else set authed true
                    $logged_in = true;
                }

                $params = [
                    'cash_request_type' => $args['plant'],
                    'cash_action' => $args['noun']
                ];

                if ($logged_in) $params['user_id'] = 1;

                if (is_array($request_params)) $params = array_merge($request_params, $params);

                $cash_request = new CASHRequest(
                    $params,
                    'direct',
                    1,
                    true,
                    $request->getMethod());

                if ($cash_request) {
                    /*return $response->withStatus($cash_request->response['status_code'])->withJson(
                        self::APIResponse($cash_request)
                    );*/

                    return $response->withHeader('Content-type', 'application/json')
                        ->withStatus($cash_request->response['status_code'])
                        ->write(
                            self::APIResponse($cash_request)
                        );
                }

            }

            if (empty($json_response)) {
                return $response->withStatus(500)->withJson(
                    self::APIResponse(false)
                );
            }

            return $json_response;
            // if we get here return 404
        })->add(new RoutingMiddleware());

        $api->run();
    }

    public static function APIResponse($response) {

        if ($response) {
            if ($response->response['payload']) {
                $response = [
                    'data' => $response->response['payload'],
                    'status' => $response->response['status_code'],
                    'status_uid' => $response->response['status_uid']
                ];
            } else {
                $response = [
                    'status' => $response->response['status_code'],
                    'status_uid' => $response->response['status_uid'],
                    'status_message' => $response->response['status_message'],
                    'error_name' => $response->response['contextual_name'],
                    'error_message' => $response->response['contextual_message']
                ];
            }
        } else {
            $response = [
                'status' => 500,
                'status_uid' => "general_500",
                'status_message' => "Server error",
                'error_name' => "There was an error while getting a response",
                'error_message' => "The request failed."
            ];
        }

        return json_encode($response);
    }
}