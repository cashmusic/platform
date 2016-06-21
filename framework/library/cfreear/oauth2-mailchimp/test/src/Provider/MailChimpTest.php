<?php

namespace CFreear\OAuth2\Client\Test\Provider;

use CFreear\OAuth2\Client\Provider\MailChimp;
use CFreear\OAuth2\Client\Provider\MailChimpResourceOwner;
use League\OAuth2\Client\Provider\AbstractProvider;
use Mockery as m;
use ReflectionClass;

class MailChimpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $provider AbstractProvider
     */
    protected $provider;

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('CFreear\OAuth2\Client\Provider\MailChimp');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function setUp()
    {
        $this->provider = new MailChimp([
            'clientId'     => 'test_client_id',
            'clientSecret' => 'test_secret',
            'redirectUri'  => 'none'
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $this->assertEquals('/oauth2/token', $uri['path']);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);
        $this->assertEquals('/oauth2/metadata', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token":"test_access_token", "token_type":"bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code',
            ['code' => 'test_authorization_code']);
        $this->assertEquals('test_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    /**
     * @expectedException League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $message = uniqid();
        $status = rand(400, 600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(' {"error":"' . $message . '"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken('authorization_code',
            ['code' => 'test_authorization_code']);
    }

    public function testMetaData()
    {
        $id = '';
        $dc = 'us1';
        $loginURL = 'https://login.mailchimp.com';
        $apiEndpoint = 'http://us1.api.mailchimp.com';

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('access_token=test_access_token&expires=3600&refresh_token=mock_refresh_token');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);
        $accountResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $accountResponse->shouldReceive('getBody')->andReturn('{"dc": "'.$dc.'","login_url": "'.$loginURL.'","api_endpoint": "'.$apiEndpoint.'"}');
        $accountResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $accountResponse->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $accountResponse);

        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'test_authorization_code']);
        /**
         * @var $account MailChimpResourceOwner
         */
        $account = $this->provider->getResourceOwner($token);

        $this->assertEquals($id, $account->getId());
        $this->assertEquals($dc, $account->getDC());
        $this->assertEquals($dc, $account->toArray()['dc']);
        $this->assertEquals($loginURL, $account->getLoginURL());
        $this->assertEquals($loginURL, $account->toArray()['login_url']);
        $this->assertEquals($apiEndpoint, $account->getAPIEndpoint());
        $this->assertEquals($apiEndpoint, $account->toArray()['api_endpoint']);
    }
}