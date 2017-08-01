<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace CASHMusic\Core\API;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {

        $cash_request = new CASHRequest([
                'cash_request_type'=>'system',
                'cash_action' => 'validateapicredentials',
                'api_key'=>$clientIdentifier,
                'api_secret'=>$clientSecret
            ],
            'direct');

        if (isset($cash_request->response['payload'])) {
            $client = new ClientEntity();
            $client->setIdentifier($cash_request->response['payload']['user_id']);
            $client->setName("api user");
            $client->setRedirectUri("");
            return $client;
        }

        return;
    }
}