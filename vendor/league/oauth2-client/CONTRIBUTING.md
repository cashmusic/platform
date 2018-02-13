# Berkontribusi

Kontribusi adalah **selamat datang** dan akan sepenuhnya **dikreditkan**.

Kami menerima kontribusi melalui Permintaan Tarik pada [Github](https://github.com/thephpleague/oauth2-client).

## Tarik Permintaan

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - Cara termudah untuk menerapkan konvensi adalah menginstal [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).

- **Tambahkan tes!** - Patch Anda tidak akan diterima jika tidak ada tes.

- **Dokumentasikan perubahan perilaku** - Pastikan README dan dokumentasi lain yang relevan selalu terjaga.

- **Pertimbangkan siklus rilis kami** - Kami mencoba mengikuti SemVer. Secara acak melanggar API publik bukanlah pilihan.

- **Buat cabang topik** - Jangan meminta kami untuk menarik dari cabang master anda.

- **Satu permintaan tarik per fitur** - Jika Anda ingin melakukan lebih dari satu hal, kirim beberapa permintaan tarik.

- **Kirimkan riwayat yang koheren** - Pastikan setiap individu melakukan permintaan tarik Anda sangat berarti. Jika Anda harus membuat beberapa perantara melakukan sementara mengembangkan, silahkan squash mereka sebelum mengirimkan.

- **Pastikan tes lulus!** - Silakan jalankan tes (lihat di bawah) sebelum mengirimkan permintaan tarik Anda, dan pastikan mereka lulus. Kami tidak akan menerima patch sampai semua tes berlalu.

- **Pastikan tidak ada pelanggaran standar pengkodean** - Silakan jalankan Kode PHP Sniffer menggunakan standar PSR-2 (lihat di bawah) sebelum mengirimkan permintaan tarik Anda. Pelanggaran akan menyebabkan kegagalan, jadi pastikan tidak ada pelanggaran. Kami tidak dapat menerima patch jika build gagal.

## Pengujian

Tes berikut harus lulus agar sebuah bangunan dianggap sukses. Jika berkontribusi, tolong pastikan pass ini sebelum mengajukan permintaan tarik.

``` bash
$ ./vendor/bin/parallel-lint src test
$ ./vendor/bin/phpunit --coverage-text
$ ./vendor/bin/phpcs src --standard=psr2 -sp
```

**Selamat**!
