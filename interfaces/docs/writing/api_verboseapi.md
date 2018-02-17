Verbose API, api_public veya api_key erişim yöntemlerine izin veren tüm isteklerin doğrudan sarmalayıcısıdır. Şu anda bunlar üye kayıt listeleri ve yeni işlem başlatma ile sınırlı ancak, kapsamı genişletmek için tam bir yetkilendirme şeması üzerinde çalışıyoruz.

Yanıt nesnesi ve ücretli yükleme, JSON olarak dönenler hariç PHP çekirdeğinin dönüşüyle hemen hemen özdeştir. API erişimine izin vermeyen bir istekte bulunmak size yasak bir statü getirecektir, ancak burada örnek bir bitiş noktası bulunmaktadır:

	/api/verbose/asset/getasset/id/2

Format basittir: /verbose/**plant**/**request**/**{parameter name}**/**{parameter value}** — ona attığınız ve yanıtladığınız gibi bir çok parametre ayrıştırır:

Format basittir: /verbose/**plant**/**request**/**{parameter name}**/**{parameter value}** — ona attığınız ve yanıtladığınız gibi bir çok parametreyi ayrıştıracaktır.

<script src="https://gist.github.com/jessevondoom/a3d384453bf053a2ca8e.js"></script>

Yetkilendirme yöntemleri hakkında daha fazlası yakında eklenecektir.
