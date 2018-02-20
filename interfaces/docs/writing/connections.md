Bağlantılar temel olarak API paketleyicileridir - üçüncü taraf hizmetleri bağlamanın soyut bir yoludur, tekliflerini kategorize eder ve her birinin çalışması için saklamamız gereken verileri tanımlar.

Bir bağlantıyı tanımlama süreci iki farklı dosyada gerçekleşir:

1. Bir json tanımı şu konumda: **/framework/settings/connections**
2. Şu konumdaki seed sınıfı: **/framework/classes/seeds**

Bağlantı JSON'da tanımlanmıştır ve şuna benzer bir görünümü olmalıdır:

 <script src="https://gist.github.com/jessevondoom/2908c44b88db934aeec5.js"></script>
 
 Bağlantı bir isme, açıklamaya ve benzersiz bir türe ihtiyaç duyar ve bağlantıyla birlikte kullanılan seed sınıf adını tanımlamanız gerekir. Olanaklar içerisinde bir seçenek vardır. Bu bağlantı türünü daraltmamızı sağlar, böylece bağlantıları bağlamsal olarak yönetici uygulamasında ve ötesinde gösterebiliriz. (Şu anda, kapsam türünü oldukça keyfi olarak seçtik, ancak ileride onları daha iyi tanımlamalıyız.) Son olarak, uyumluluğu listeleyen bir sıra var - bağlantı tek kullanıcılı modda mı, çok kullanıcılı modda mı yoksa her ikisinde mi çalışıyor?
