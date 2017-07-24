<?php

namespace Chadicus\Slim\OAuth2\Routes;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Slim route for oauth2 receive-code.
 */
final class ReceiveCode implements RouteCallbackInterface
{
    const ROUTE = '/receive-code';

    /**
     * The slim framework view Helper.
     *
     * @var object
     */
    private $view;

    /**
     * The template for /receive-code
     *
     * @var string
     */
    private $template;

    /**
     * Construct a new instance of ReceiveCode route.
     *
     * @param object $view     The slim framework view helper.
     * @param string $template The template for /receive-code.
     *
     * @throws \InvalidArgumentException Thrown if $view is not an object implementing a render method.
     */
    public function __construct($view, $template = '/receive-code.phtml')
    {
        if (!is_object($view) || !method_exists($view, 'render')) {
            throw new \InvalidArgumentException('$view must implement a render() method');
        }

        $this->view = $view;
        $this->template = $template;
    }

    /**
     * Invoke this route callback.
     *
     * @param ServerRequestInterface $request   Represents the current HTTP request.
     * @param ResponseInterface      $response  Represents the current HTTP response.
     * @param array                  $arguments Values for the current route’s named placeholders.
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $arguments = [])
    {
        $queryParams = $request->getQueryParams();
        $code = array_key_exists('code', $queryParams) ? $queryParams['code'] : null;
        $this->view->render($response, $this->template, ['code' => $code]);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
