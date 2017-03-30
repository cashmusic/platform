<?php

namespace CFreear\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MailChimpResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     * @var
     */
    protected $response;

    /**
     * Set response
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns empty id as MailChimp doesn't respond with an identifier.
     *
     * @return string
     */
    public function getId()
    {
        return '';
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Get the data center identifier.
     *
     * @return array
     */
    public function getDC()
    {
        return $this->response['dc'];
    }

    /**
     * Get the api endpoint.
     *
     * @return array
     */
    public function getAPIEndpoint()
    {
        return $this->response['api_endpoint'];
    }

    /**
     * Get the login url.
     *
     * @return array
     */
    public function getLoginURL()
    {
        return $this->response['login_url'];
    }
}
