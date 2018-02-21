Bağlantılar temel olarak API paketleyicileridir - üçüncü taraf hizmetleri bağlamanın soyut bir yoludur, tekliflerini kategorize eder ve her birinin çalışması için saklamamız gereken verileri tanımlar.

Bir bağlantıyı tanımlama süreci iki farklı dosyada gerçekleşir:

1. Bir json tanımı şu konumda: **/framework/settings/connections**
2. Bir çekirdek sınıfı şu konumda: **/framework/classes/seeds**

Bağlantı JSON'da tanımlanmıştır ve şuna benzer bir görünümü olmalıdır:

 <script src="https://gist.github.com/jessevondoom/2908c44b88db934aeec5.js"></script>
 
Bağlantı bir isme, açıklamaya ve benzersiz bir türe ihtiyaç duyar ve bağlantıyla birlikte kullanılan çekirdek sınıf adını tanımlamanız gerekir. Olanaklar içerisinde bir seçenek vardır. Bu bağlantı türünü daraltmamızı sağlar, böylece bağlantıları bağlamsal olarak yönetici uygulamasında ve ötesinde gösterebiliriz. (Şu anda, kapsam türünü oldukça keyfi olarak seçtik, ancak ileride onları daha iyi tanımlamalıyız.) Son olarak, uyumluluğu listeleyen bir düzen var - bağlantı tek kullanıcılı modda mı, çok kullanıcılı modda mı yoksa her ikisinde mi çalışıyor?

Bağlantının tek kullanıcılı ve çok kullanıcılı sürümleri için gerekli farklı verileri tanımlayabileceğinizi unutmayın. (Anahtarları/sırları çok kullanıcılı modda saklamaktan çekiniyoruz.)

#### Çekirdek sınıfı
Çoğunlukla üçüncü parti API'lere bağlanma biçiminde, çekirdekler belirli işlevleri yerine getirirken, platformun daha büyük planında işletme istekleri ele alır. Çekirdeklerin esnek olması gerektiği için yapıları oldukça eğlencelidir, ancak benzer çekirdekler arasında bir grup ortak işlev göreceksiniz - S3 ve Google Drive için çekirdekleri kontrol edin.

Bunu yakında bir kapsam bazında daha somut bir şekilde tanımlayacağız, ancak şu an için lütfen benzer hizmetlere bakın ve onlardan örnek alın. İşletme seviyesinde mümkün olduğunca soyutlayabilmemiz için çekirdek seviyesinde bulabildiğimiz kadar çok sayıda tekdüzelik istiyoruz.
