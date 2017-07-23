<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 7/23/17
 * Time: 1:27 PM
 */

namespace CASHMusic\Core;

use Slim\App as Slim;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class CASHAPI
{

    protected $api;

    public function __construct()
    {
        CASHSystem::startUp();

        $this->app = new Slim();
        // the verb is
        $api = $this;
        $this->app->get('/{plant}/{noun}', function ($request, $response, $args) use ($api) {
            $plants = CASHRequest::buildPlantArray();

            if (array_key_exists($request->getAttribute('plant'), $plants)) {
                $plant = $plants[$request->getAttribute('plant')];
                $noun = $request->getAttribute('noun');
                $method = $request->getMethod();

                if ($response = $api->validateRequestedRoute($plant, $noun, $method, false)) {
                    // parse response
                    echo "wee";
                }

                // r
                return false;
                $namespace = '\CASHMusic\Plants\\';

                //$class_name = $namespace.$directory.$plant;


                /*$this->plant = new $class_name($this->request_method,$this->request);
                $this->response = $this->plant->processRequest('api');*/
            }


        });

        $this->app->run();
    }

    /**
     * @param $plant
     */
    public static function getPlantDirectory($plant)
    {
         return CASH_PLATFORM_ROOT."/classes/plants/".str_replace("Plant", "", $plant)."/";
    }

    public function getRoutingTables($plant) {
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

    public function validateRequestedRoute($plant, $noun, $method, $auth=false) {
        list($restful_routes, $soap_routes) = $this->getRoutingTables($plant);

        if ($restful_route = (isset($restful_routes[$noun])) ? $restful_routes[$noun] : false) {
            // check method + ACL + $auth

            if (isset($restful_route['verbs'][$method])) {
                $verb = $restful_route['verbs'][$method];

                if ($verb['authrequired']) {
                    if ($auth) {
                        // do request
                    } else {
                        // return 403 response
                    }
                } else {
                    // do request
                }
            }

            return true;
        }

        if ($soap_route = (isset($soap_routes[$noun])) ? $soap_routes[$noun] : false) {
            // check method ACL + $auth
            if (in_array("api_public", $soap_route['security'])) {
                // do request
                CASHSystem::errorLog("yay");
            } elseif (in_array("api_private", $soap_route['security'])) {
                if ($auth) {
                    // do request
                } else {
                    // return 403 response
                }
            }

            return true;
        }
    }
}