<?php


namespace AdamPaterson\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class StripeResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
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
     * Get Stripe account id
     *
     * @return string
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Return all of the account details available as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * The primary user’s email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * The text that will appear on credit card statements
     *
     * @return string
     */
    public function getStatementDescriptor()
    {
        return $this->response['statement_descriptor'];
    }

    /**
     * A publicly shareable email address that can be reached for support for this account
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->response['display_name'];
    }

    /**
     * The timezone used in the Stripe dashboard for this account.
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->response['timezone'];
    }

    /**
     * Whether or not account details have been submitted yet.
     * Standalone accounts cannot receive transfers before this is true.
     *
     * @return bool
     */
    public function getDetailsSubmitted()
    {
        return $this->response['details_submitted'];
    }

    /**
     * Whether or not the account can create live charges
     *
     * @return bool
     */
    public function getChargesEnabled()
    {
        return $this->response['charges_enabled'];
    }

    /**
     * Whether or not Stripe will send automatic transfers for this account.
     * This is only false when Stripe is waiting for additional information from the account holder.
     *
     * @return bool
     */
    public function getTransfersEnabled()
    {
        return $this->response['transfers_enabled'];
    }

    /**
     * The currencies this account can submit when creating charges
     *
     * @return array
     */
    public function getCurrenciesSupported()
    {
        return $this->response['currencies_supported'];
    }

    /**
     * The currency this account has chosen to use as the default
     *
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->response['default_currency'];
    }

    /**
     * The country of the account
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->response['country'];
    }

    /**
     * The object requested. Will always return "account"
     *
     * @return string
     */
    public function getObject()
    {
        return $this->response['object'];
    }

    /**
     * The publicly visible name of the business
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->response['business_name'];
    }

    /**
     * A publicly shareable URL that can be reached for support for this account
     *
     * @return string
     */
    public function getBusinessUrl()
    {
        return $this->response['business_url'];
    }

    /**
     * The publicly visible support phone number for the business
     *
     * @return string
     */
    public function getSupportPhone()
    {
        return $this->response['support_phone'];
    }

    /**
     * The publicly visible logo url
     *
     * @return string
     */
    public function getBusinessLogo()
    {
        return $this->response['business_logo'];
    }

    /**
     * Updatable Stripe objects
     *
     * @see https://stripe.com/docs/api/curl#metadata
     * @return array
     */
    public function getMetaData()
    {
        return $this->response['metadata'];
    }

    /**
     * Whether or not the account is managed by your platform.
     * Returns null if the account was not created by a platform.
     *
     * @return bool
     */
    public function getManaged()
    {
        return $this->response['managed'];
    }
}
