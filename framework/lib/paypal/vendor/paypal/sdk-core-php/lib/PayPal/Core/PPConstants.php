<?php
namespace PayPal\Core;

class PPConstants {
    const SDK_NAME = 'sdk-core-php';
    const SDK_VERSION = '3.2.4';

    const MERCHANT_SANDBOX_SIGNATURE_ENDPOINT = "https://api-3t.sandbox.paypal.com/2.0";
    const MERCHANT_SANDBOX_CERT_ENDPOINT = "https://api.sandbox.paypal.com/2.0";
    const PLATFORM_SANDBOX_ENDPOINT = "https://svcs.sandbox.paypal.com/";
    const REST_SANDBOX_ENDPOINT = "https://api.sandbox.paypal.com/";
    const IPN_SANDBOX_ENDPOINT = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    const OPENID_REDIRECT_SANDBOX_URL = "https://www.sandbox.paypal.com/webapps/auth/protocol/openidconnect";

    const MERCHANT_LIVE_SIGNATURE_ENDPOINT = "https://api-3t.paypal.com/2.0";
    const MERCHANT_LIVE_CERT_ENDPOINT = "https://api.paypal.com/2.0";
    const PLATFORM_LIVE_ENDPOINT = "https://svcs.paypal.com/";
    const REST_LIVE_ENDPOINT = "https://api.paypal.com/";
    const IPN_LIVE_ENDPOINT = "https://www.paypal.com/cgi-bin/webscr";
    const OPENID_REDIRECT_LIVE_URL = "https://www.paypal.com/webapps/auth/protocol/openidconnect";

    const MERCHANT_TLS_SIGNATURE_ENDPOINT = "https://test-api-3t.sandbox.paypal.com/2.0";
    const MERCHANT_TLS_CERT_ENDPOINT = "https://test-api.sandbox.paypal.com/2.0";
    const PLATFORM_TLS_ENDPOINT = "https://test-svcs.sandbox.paypal.com/";
    const REST_TLS_ENDPOINT = "https://test-api.sandbox.paypal.com/";
    const IPN_TLS_ENDPOINT = "https://www.test-sandbox.paypal.com/cgi-bin/webscr";
    const OPENID_REDIRECT_TLS_URL = "https://www.test-sandbox.paypal.com/webapps/auth/protocol/openidconnect";
}
