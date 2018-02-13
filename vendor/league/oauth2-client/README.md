# Klien OAuth 2.0

Paket ini memudahkan Anda mengintegrasikan aplikasi Anda dengan penyedia layanan [OAuth 2.0](http://oauth.net/2/).

[![Gitter Chat](https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square)](https://gitter.im/thephpleague/oauth2-client)
[![Source Code](http://img.shields.io/badge/source-thephpleague/oauth2--client-blue.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client)
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-client.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-client/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-client)
[![HHVM Status](https://img.shields.io/hhvm/league/oauth2-client.svg?style=flat-square)](http://hhvm.h4cc.de/package/league/oauth2-client)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-client/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-client/)
[![Coverage Status](https://img.shields.io/coveralls/thephpleague/oauth2-client/master.svg?style=flat-square)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

---

Kita semua terbiasa melihat mereka "Terhubung dengan Facebook / Google / dll." tombol di internet, dan integrasi jaringan sosial merupakan fitur penting dari kebanyakan aplikasi web akhir-akhir ini. Banyak dari situs ini menggunakan standar otentikasi dan otorisasi yang disebut OAuth 2.0 ([RFC 6749](http://tools.ietf.org/html/rfc6749)).

Perpustakaan klien OAuth 2.0 ini akan bekerja sama dengan penyedia OAuth manapun yang sesuai dengan standar OAuth 2.0. Out-of-the-box, kami menyediakan `GenericProvider` yang dapat digunakan untuk terhubung ke penyedia layanan yang menggunakan [Bearer tokens](http://tools.ietf.org/html/rfc6750) (see example below).

Banyak penyedia layanan menyediakan fungsionalitas tambahan di atas dan di luar standar OAuth 2.0. Untuk alasan ini, perpustakaan ini mudah diperluas dan dibungkus untuk mendukung perilaku tambahan ini. Kami menyediakan link ke [all known provider clients extending this library](README.PROVIDERS.md) (i.e. Facebook, GitHub, Google, Instagram, LinkedIn, etc.). Jika penyedia Anda tidak ada dalam daftar, silakan menambahkannya.

Paket ini sesuai dengan [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][].Jika Anda memperhatikan kepatuhan kepatuhan, kirimkan tambalan melalui permintaan tarik. Jika Anda menarik untuk berkontribusi ke perpustakaan ini, silakan lihat di perpustakaan kami [contributing guidelines](CONTRIBUTING.md).

## Persyaratan

Versi PHP berikut didukung.

* PHP 5.5
* PHP 5.6
* PHP 7.0
* HHVM

## Penyedia

Daftar penyedia Liga PHP resmi, dan juga penyedia pihak ketiga, dapat ditemukan di [providers list README](README.PROVIDERS.md).

Untuk membangun provider anda sendiri, silahkan lihat [provider guide README](README.PROVIDER-GUIDE.md).

## Pemakaian

**Dalam kebanyakan kasus, Anda ingin menggunakan perpustakaan klien penyedia khusus daripada pustaka dasar ini.**

Melihat [README.PROVIDERS.md](README.PROVIDERS.md) untuk melihat daftar perpustakaan klien penyedia.

Jika menggunakan Komposer untuk meminta perpustakaan klien penyedia tertentu, Anda **tidak perlu juga meminta perpustakaan ini**. Komposer akan menangani dependensi untuk Anda.

### Hibah Kode Otorisasi

Contoh berikut menggunakan out-of-the-box `GenericProvider` yang disediakan oleh perpustakaan ini. Jika Anda mencari penyedia tertentu (yaitu Facebook, Google, GitHub, dll.), Lihatlah kami [list of provider client libraries](README.PROVIDERS.md). **PETUNJUK: Anda mungkin mencari penyedia tertentu.**

Jenis pemberian kode otorisasi adalah jenis hibah yang paling umum digunakan saat mengautentikasi pengguna dengan layanan pihak ketiga. Jenis hibah ini menggunakan klien (perpustakaan ini), server (penyedia layanan), dan pemilik sumber daya (pengguna dengan kredensial pada sumber daya yang dilindungi atau yang dimiliki) untuk meminta akses ke sumber daya yang dimiliki oleh pengguna. Hal ini sering disebut sebagai OAuth_berkaki tiga, karena ada tiga pihak yang terlibat.

Contoh berikut menggambarkan hal ini dengan menggunakan aplikasi demo OAuth 2.0 [Brent Shaffer](di bawah https://github.com/bshaffer 2.0) yang bernama **Lock'd In**. Saat menjalankan kode ini, Anda akan dialihkan ke Lock'd In, di mana Anda akan diminta memberi otorisasi kepada klien untuk mengajukan permintaan ke sumber daya atas nama Anda.

Sekarang, Anda tidak benar-benar memiliki akun di Lock'd In, tapi demi contoh ini, bayangkan Anda sudah masuk log pada Lock'd In saat Anda diarahkan ke sana.

```php
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'demoapp',    // The client ID assigned to you by the provider
    'clientSecret'            => 'demopass',   // The client password assigned to you by the provider
    'redirectUri'             => 'http://example.com/your-redirect-url/',
    'urlAuthorize'            => 'http://brentertainment.com/oauth2/lockdin/authorize',
    'urlAccessToken'          => 'http://brentertainment.com/oauth2/lockdin/token',
    'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo $accessToken->getToken() . "\n";
        echo $accessToken->getRefreshToken() . "\n";
        echo $accessToken->getExpires() . "\n";
        echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "\n";

        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $provider->getResourceOwner($accessToken);

        var_export($resourceOwner->toArray());

        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'http://brentertainment.com/oauth2/lockdin/resource',
            $accessToken
        );

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}
```

### Menyegarkan Token

Setelah aplikasi Anda diberi wewenang, Anda dapat menyegarkan kembali token kedaluwarsa menggunakan tanda refresh daripada melalui keseluruhan proses mendapatkan token merek baru. Untuk melakukannya, cukup gunakan kembali token penyegaran dari penyimpanan data Anda untuk meminta penyegaran.

_Contoh ini menggunakan [Brent Shaffer's](https://github.com/bshaffer) demo aplikasi OAuth 2.0 bernama **Lock'd In**. Lihat contoh kode otorisasi di atas, untuk lebih jelasnya._

```php
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'demoapp',    // The client ID assigned to you by the provider
    'clientSecret'            => 'demopass',   // The client password assigned to you by the provider
    'redirectUri'             => 'http://example.com/your-redirect-url/',
    'urlAuthorize'            => 'http://brentertainment.com/oauth2/lockdin/authorize',
    'urlAccessToken'          => 'http://brentertainment.com/oauth2/lockdin/token',
    'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'
]);

$existingAccessToken = getAccessTokenFromYourDataStore();

if ($existingAccessToken->hasExpired()) {
    $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $existingAccessToken->getRefreshToken()
    ]);

    // Purge old access token and store new access token to your data store.
}
```

### Kredensial Password Pemilik Sumber Daya

Beberapa penyedia layanan mengizinkan Anda melewati langkah kode otorisasi untuk menukar kredensial pengguna (nama pengguna dan kata sandi) untuk sebuah token akses. Ini disebut sebagai jenis pemberian kredensial "sumber daya pemilik sumber daya".

Menurut [section 1.3.3](http://tools.ietf.org/html/rfc6749#section-1.3.3) dari standar OAuth 2.0 (penekanan ditambahkan):

> Kredensial **hanya boleh digunakan bila ada tingkat kepercayaan yang tinggi**
> antara pemilik sumber daya dan klien (mis., klien adalah bagian dari
> sistem operasi perangkat atau aplikasi yang sangat istimewa), dan bila lainnya
> jenis hibah otorisasi tidak tersedia (seperti kode otorisasi).

**Kami tidak menyarankan menggunakan jenis hibah ini jika penyedia layanan mendukung jenis pemberian kode otorisasi (lihat di atas), karena ini memperkuat [password anti-pattern](https://agentile.com/the-password-anti-pattern) dengan mengizinkan pengguna berpikir tidak apa-apa untuk mempercayai aplikasi pihak ketiga dengan nama pengguna dan kata sandi mereka.**

Yang mengatakan, ada kasus penggunaan di mana kredensial kredensial sumber daya pemilik sumber dapat diterima dan berguna. Berikut adalah contoh menggunakannya dengan [Brent Shaffer's](https://github.com/bshaffer) demo aplikasi OAuth 2.0 bernama **Lock'd In**. Lihat contoh kode otorisasi di atas, untuk rincian lebih lanjut tentang aplikasi demo Lock'd In.

``` php
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'demoapp',    // The client ID assigned to you by the provider
    'clientSecret'            => 'demopass',   // The client password assigned to you by the provider
    'redirectUri'             => 'http://example.com/your-redirect-url/',
    'urlAuthorize'            => 'http://brentertainment.com/oauth2/lockdin/authorize',
    'urlAccessToken'          => 'http://brentertainment.com/oauth2/lockdin/token',
    'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'
]);

try {

    // Try to get an access token using the resource owner password credentials grant.
    $accessToken = $provider->getAccessToken('password', [
        'username' => 'demouser',
        'password' => 'testpass'
    ]);

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

    // Failed to get the access token
    exit($e->getMessage());

}
```

### Hibah Kredensial Klien

Bila aplikasi Anda bertindak atas nama sendiri untuk mengakses sumber daya yang dikontrol/dimiliki di penyedia layanan, mungkin aplikasi tersebut menggunakan jenis pemberian kredensial klien. Ini paling baik digunakan bila kredensial untuk aplikasi Anda disimpan secara pribadi dan tidak pernah terpapar (misalnya melalui browser web, dsb.) Kepada pengguna akhir. Fungsi jenis hibah ini sama dengan jenis pemberian kredensial kata sandi pemilik sumber daya, tetapi tidak meminta nama pengguna atau kata sandi pengguna. Ini hanya menggunakan ID klien dan rahasia yang dikeluarkan untuk klien Anda oleh penyedia layanan.

Tidak seperti contoh sebelumnya, hal berikut tidak bekerja melawan penyedia layanan demo yang berfungsi. Ini disediakan hanya untuk contoh saja.

``` php
// Note: the GenericProvider requires the `urlAuthorize` option, even though
// it's not used in the OAuth 2.0 client credentials grant type.

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'XXXXXX',    // The client ID assigned to you by the provider
    'clientSecret'            => 'XXXXXX',    // The client password assigned to you by the provider
    'redirectUri'             => 'http://my.example.com/your-redirect-url/',
    'urlAuthorize'            => 'http://service.example.com/authorize',
    'urlAccessToken'          => 'http://service.example.com/token',
    'urlResourceOwnerDetails' => 'http://service.example.com/resource'
]);

try {

    // Try to get an access token using the client credentials grant.
    $accessToken = $provider->getAccessToken('client_credentials');

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

    // Failed to get the access token
    exit($e->getMessage());

}
```

### Menggunakan proxy

Anda bisa menggunakan proxy untuk melakukan debug panggilan HTTP yang dilakukan ke penyedia. Yang perlu Anda lakukan adalah mengatur opsi `proxy` dan` verify` saat membuat instance Provider Anda. Pastikan Anda mengaktifkan proxy SSL di proxy Anda.

``` php
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'XXXXXX',    // The client ID assigned to you by the provider
    'clientSecret'            => 'XXXXXX',    // The client password assigned to you by the provider
    'redirectUri'             => 'http://my.example.com/your-redirect-url/',
    'urlAuthorize'            => 'http://service.example.com/authorize',
    'urlAccessToken'          => 'http://service.example.com/token',
    'urlResourceOwnerDetails' => 'http://service.example.com/resource',
    'proxy'                   => '192.168.0.1:8888',
    'verify'                  => false
]);
```

## Memasang

Melalui komposer

``` bash
$ composer require league/oauth2-client
```

## Berkontribusi

Silahkan lihat [CONTRIBUTING](https://github.com/thephpleague/oauth2-client/blob/master/CONTRIBUTING.md) untuk rinciannya

## Lisensi

Lisensi MIT (MIT). Silahkan lihat [License File](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE) untuk informasi lebih lanjut.


[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md
