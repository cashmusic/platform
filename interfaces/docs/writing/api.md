Uygulamamız **/interfaces/api/** reposunda bulunan bağımsız bir uygulamadır ve lokal olarak test etmek için cashmusic.org adresindeki /api/ URL konumundan ulaşılabilir. Yönetici uygulaması gibi, tüm yapılandırma, uygulama kökünde bulunan constants.php dosyası tarafından işlenir, ve tüm istekler mod_rewrite üzerinden controller.php betiği vasıtasıyla yönlendirilir.

Ana uygulama işlevselliği **/interfaces/api/classes/APICore.php** adresinde bulunan **APICore** sınıfı tarafından işlenir.

Uygulama, herhangi bir istek için JSON nesneleri döndürür. **/api** konumundan **/** talep etmek basitçe sürüm numarası ile birlikte bir merhaba verir:

<script src="https://gist.github.com/jessevondoom/b51b3ec5bee653d46cff.js"></script>

Hatalı bir istek, durum kodu ve mesajlarla birlikte daha standart bir format döndürür:

<script src="https://gist.github.com/jessevondoom/c01eaae218cb6129acbf.js"></script>

Daha sonra aşağıdaki verbose ve RESTful dökümanlarında başarılı istekleri detaylandıracağız.
