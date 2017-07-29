<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 7/23/17
 * Time: 1:27 PM
 */

namespace CASHMusic\Core;

use CASHMusic\Core\API\AccessTokenRepository;
use CASHMusic\Core\API\AuthCodeRepository;
use CASHMusic\Core\API\AuthMiddleware;
use CASHMusic\Core\API\ClientRepository;
use CASHMusic\Core\API\RefreshTokenRepository;
use CASHMusic\Core\API\RoutingMiddleware;
use CASHMusic\Core\API\ScopeRepository;
use CASHMusic\Core\API\UserEntity;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Slim\App as Slim;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Middleware\AuthorizationServerMiddleware;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Stream;

class CASHAPI
{

    public function __construct()
    {
        CASHSystem::startUp(false);

        list($accessTokenRepository, $server) = $this->getAuthorizationServer();

        $api = new Slim(['settings' => [
            'addContentLengthHeader' => false,
        ]]);


        $api->post('/access_token', function (ServerRequestInterface $request, ResponseInterface $response) use ($api, $server) {

            try {

                $server->enableGrantType(
                    new ClientCredentialsGrant(),
                    new \DateInterval('PT1H') // access tokens will expire after 1 hour
                );


                // Try to respond to the request
                return $server->respondToAccessTokenRequest($request, $response);

            } catch (\League\OAuth2\Server\Exception\OAuthServerException $exception) {

                // All instances of OAuthServerException can be formatted into a HTTP response
                return $exception->generateHttpResponse($response);

            } catch (\Exception $exception) {

                // Unknown exception
                $body = new Stream('php://temp', 'r+');
                $body->write($exception->getMessage());
                return $response->withStatus(500)->withBody($body);

            }
        });


        $api->any('/{plant}/{noun}', function ($request, $response, $args) use (&$authorization) {

            if ($route = $request->getAttribute('route_settings')) {
                $request_params = $request->getQueryParams();
                if (isset($request_params['p'])) unset($request_params['p']);

                $params = [
                    'cash_request_type' => $args['plant'],
                    'cash_action' => $args['noun']
                ];

                if (1==1) $params['user_id'] = 1;

                if (is_array($request_params)) $params = array_merge($request_params, $params);

                $cash_request = new CASHRequest(
                    $params,
                    'direct',
                    1,
                    true,
                    $request->getMethod());

                if ($cash_request) {
                    return $response->withStatus($cash_request->response['status_code'])->withJson(
                        self::APIResponse($cash_request)
                    );
                }
            }

            return $response->withStatus(404)->withJson(self::APIResponse(false));

            // if we get here return 404
        })->add(new AuthMiddleware($accessTokenRepository))->add(new RoutingMiddleware());

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
                'status' => 404,
                'status_uid' => "general_404",
                'status_message' => "Route not found, or server error",
                'error_name' => "There was an error while getting a response",
                'error_message' => "The request failed."
            ];
        }

        return ($response);
    }

    /**
     * @return array
     */
    public function getAuthorizationServer()
    {
        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface

        $privateKey = CASH_PLATFORM_ROOT . "/settings/keys/private.key";
        $encryptionKey = 'X7jv9J1UcOE00EgRGzcJJ6boPXFASE3idhwPUoWsw5k=';

        // Setup the authorization server
        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        return array($accessTokenRepository, $server);
    }
}