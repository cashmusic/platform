<?php

use PayPal\Handler\IPPHandler;

class MockNVPClass {
    public function toNVPString() {
        return 'invoiceID=INV2-6657-UHKM-3LWC-JHF7';
    }
}

class MockHandler  implements IPPHandler {

    public function handle($httpConfig, $request, $options) {
        $config = $options['config'];
        $httpConfig->setUrl('https://svcs.sandbox.paypal.com/Invoice/GetInvoiceDetails');
        $httpConfig->addHeader('X-PAYPAL-REQUEST-DATA-FORMAT', 'NV');
        $httpConfig->addHeader('X-PAYPAL-RESPONSE-DATA-FORMAT', 'NV');
        $httpConfig->addHeader('X-PAYPAL-SECURITY-USERID', 'jb-us-seller_api1.paypal.com');
        $httpConfig->addHeader('X-PAYPAL-SECURITY-PASSWORD', 'WX4WTU3S8MY44S7F');
        $httpConfig->addHeader('X-PAYPAL-SECURITY-SIGNATURE', 'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy');
    }
}

