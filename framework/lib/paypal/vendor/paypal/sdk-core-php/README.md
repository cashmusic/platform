### PayPal Core SDK

## Please Note
> **The Payment Card Industry (PCI) Council has [mandated](http://blog.pcisecuritystandards.org/migrating-from-ssl-and-early-tls) that early versions of TLS be retired from service.  All organizations that handle credit card information are required to comply with this standard. As part of this obligation, PayPal is updating its services to require TLS 1.2 for all HTTPS connections. At this time, PayPal will also require HTTP/1.1 for all connections. [Click here](https://github.com/paypal/tls-update) for more information**

> A new `mode` has been created to test if your server/machine handles TLSv1.2 connections. Please use `tls` mode instead of `sandbox` to verify. You can return back to `sandbox` mode once you have verified.

#### Prerequisites

 * PHP 5.3 and above
 * curl extension with support for OpenSSL
 * PHPUnit 3.5 for running test suite (Optional)
 * Composer

#### Configuration
  
 
#### OpenID Connect Integration

   1. Redirect your buyer to `PPOpenIdSession::getAuthorizationUrl($redirectUri, array('openid', 'address'));` to obtain authorization. The second argument is the list of access privileges that you want from the buyer.
   2. Capture the authorization code that is available as a query parameter (`code`) in the redirect url
   3. Exchange the authorization code for a access token, refresh token, id token combo


```php
    $token = PPOpenIdTokeninfo::createFromAuthorizationCode(
		array(
			'code' => $authCode
		)
	);
```
   4. The access token is valid for a predefined duration and can be used for seamless XO or for retrieving user information


```php
   $user = PPOpenIdUserinfo::getUserinfo(
		array(
			'access_token' => $token->getAccessToken()
		)	
	);
```
   5. If the access token has expired, you can obtain a new access token using the refresh token from the 3'rd step.

```php
   $token->createFromRefreshToken(array('openid', 'address'));
```
   6. Redirect your buyer to `PPOpenIdSession::getLogoutUrl($redirectUri, $idToken);` to log him out of paypal. 
