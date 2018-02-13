# Klien OAuth 2.0

## Panduan Penyedia Jasa

Penyedia baru dapat dibuat dengan menyalin tata letak paket yang ada. Lihat
itu [list of providers](README.PROVIDERS.md) untuk contoh yang bagus

Saat memilih nama untuk paket Anda, jangan gunakan vendor `league`
awalan, karena ini berarti secara resmi didukung. Anda harus menggunakan sendiri
username sebagai awalan vendor, dan tambahkan `oauth2-` ke nama paket yang akan dibuat
jelas bahwa paket Anda bekerja dengan OAuth2 Client. Misalnya, jika Anda GitHub
nama pengguna adalah "santa", dan Anda menerapkan pustaka "giftpay" OAuth2, a
nama baik untuk paket komposer anda adalah `santa / oauth2-giftpay`.

### Melaksanakan penyedia Anda sendiri

Jika Anda bekerja dengan layanan oauth2 tidak didukung out-of-the-box atau oleh
Paket yang ada, cukup mudah untuk mengimplementasikannya sendiri. Cukup perpanjang
[`League\OAuth2\Client\Provider\AbstractProvider`](src/Provider/AbstractProvider.php)
dan menerapkan metode abstrak yang dibutuhkan:

```php
abstract public function getBaseAuthorizationUrl();
abstract public function getBaseAccessTokenUrl(array $params);
abstract public function getResourceOwnerDetailsUrl(AccessToken $token);
abstract protected function getDefaultScopes();
abstract protected function checkResponse(ResponseInterface $response, $data);
abstract protected function createResourceOwner(array $response, AccessToken $token);
```

Masing-masing metode abstrak ini berisi dokblock yang menentukan harapan mereka
dan perilaku tipikal. Setelah Anda memperpanjang kelas ini, Anda bisa langsung mengikuti
itu [usage example in the README](README.md#usage) menggunakan yang baru `Provider`.

Jika Anda ingin menggunakan `Provider` untuk membuat permintaan otentik ke
layanan, Anda juga perlu menentukan bagaimana Anda memberikan token ke
layanan. Jika ini dilakukan melalui header, Anda harus mengganti metode ini:

```php
protected function getAuthorizationHeaders($token = null);
```

Paket ini dilengkapi dengan sifat untuk menerapkan otorisasi `Bearer`.
Untuk menggunakan ini, Anda hanya perlu memasukkan sifat di kelas `Provider` Anda:
 
```php
<?php
class SomeProvider extends AbstractProvider
{
    use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
    
    /** ... **/
}
```


### Pengidentifikasi pemilik sumber daya dalam respon token akses

Dalam layanan di mana pemilik sumber daya adalah seseorang, terkadang pemilik sumber daya
disebut sebagai pengguna akhir.

Kami telah memutuskan untuk menghapus sebanyak mungkin rincian pemilik sumber daya,
karena ini bukan bagian dari spesifikasi OAuth 2.0 dan sangat spesifik untuk masing-masing
penyedia layanan. Ini memberikan fleksibilitas yang lebih besar untuk setiap provider, memungkinkan
mereka untuk menangani detail implementasi bagi pemilik sumber daya.

`AbstractProvider` tidak menentukan pengenal pemilik sumber daya token akses. ini
tanggung jawab kelas penyedia untuk mengatur konstanta `ACCESS_TOKEN_RESOURCE_OWNER_ID`
ke nilai string kunci yang digunakan dalam respon token akses untuk mengidentifikasi
pemilik sumber daya

```php
/**
 * @var string Key used in the access token response to identify the resource owner.
 */
const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;
```

Setelah ini diatur pada operator Anda, saat memanggil `AbstractProvider::getAccessToken ()`,
`AccessToken` dikembalikan akan memiliki properti  `$resourceOwnerId` yang mungkin Anda inginkan
ambil dengan memanggil `AccessToken::getResourceOwnerId()`.

Langkah selanjutnya adalah menerapkan `AbstractProvider::createResourceOwner()` metode. Ini
metode menerima sebagai parameter array respon dan `AccessToken`. Anda bisa menggunakannya
informasi ini untuk meminta rincian pemilik sumber daya dari layanan Anda dan
membangun dan mengembalikan sebuah benda yang diimplementasikan
[`League\OAuth2\Client\Provider\ResourceOwnerInterface`](src/Provider/ResourceOwnerInterface.php).
Objek ini dikembalikan saat memanggil `AbstractProvider::getResourceOwner()`.

### Jadikan pejabat gateway anda

Jika Anda ingin mentransfer penyedia Anda ke organisasi GitHub `thephpleague`
dan menambahkannya ke daftar penyedia yang didukung secara resmi, tolong buka sebuah tarikan
permintaan paket thephpleague/oauth2-client. Sebelum penyedia baru akan
diterima, mereka harus memiliki cakupan kode uji 100% unit, dan ikuti
konvensi dan gaya kode yang digunakan pada penyedia Klien OAuth2 lainnya.
