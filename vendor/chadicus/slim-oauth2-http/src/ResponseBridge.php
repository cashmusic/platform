<?php
namespace Chadicus\Slim\OAuth2\Http;

use OAuth2;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * Static utility class for bridging OAuth2 responses to PSR-7 responses.
 */
class ResponseBridge
{
    /**
     * Copies values from the given Oauth2\Response to a PSR-7 Http Response.
     *
     * @param OAuth2\Response $oauth2Response The OAuth2 server response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    final public static function fromOauth2(OAuth2\Response $oauth2Response)
    {
        $headers = [];
        foreach ($oauth2Response->getHttpHeaders() as $key => $value) {
            $headers[$key] = explode(', ', $value);
        }

        $stream = fopen('php://temp', 'r+');
        if (!empty($oauth2Response->getParameters())) {
            fwrite($stream, $oauth2Response->getResponseBody());
            rewind($stream);
        }

        return new Response(new Stream($stream), $oauth2Response->getStatusCode(), $headers);
    }
}
