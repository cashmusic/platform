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
use Chadicus\Slim\OAuth2\Routes;
use Chadicus\Slim\OAuth2\Middleware;
use Slim\Http;
use OAuth2\Server;
use Slim\Views\PhpRenderer;
use OAuth2\Storage;
use OAuth2\GrantType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class CASHAPI
{

    protected $api;

    public function __construct()
    {
        CASHSystem::startUp(false);

        $cash_db_settings = CASHSystem::getSystemSettings();

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
        );

        $this->app = new Slim([
            'settings' => [
                'determineRouteBeforeAppMiddleware' => true,
            ]
        ]);
        $renderer = new PhpRenderer( __DIR__ . '/vendor/chadicus/slim-oauth2-routes/templates');

        $authorization = new Middleware\Authorization($server, $this->app->getContainer());
        CASHSystem::errorLog($authorization, false);
        $this->app->map(['GET', 'POST'], Routes\Authorize::ROUTE, new Routes\Authorize($server, $renderer))->setName('authorize');
        $this->app->post(Routes\Token::ROUTE, new Routes\Token($server))->setName('token');
        $this->app->map(['GET', 'POST'], Routes\ReceiveCode::ROUTE, new Routes\ReceiveCode($renderer))->setName('receive-code');

        $this->app->any('/{plant}/{noun}', function ($request, $response, $args) use (&$authorization) {



        })->add($authorization);

        $this->app->run();
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