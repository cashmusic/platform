Platform yönetim uygulaması (**/interfaces/admin**) ön denetleyiciyle inşa edilmiş oldukça düz bir MVC stili web uygulamasıdır, her rota için bireysel denetleyiciler, mustache görüntülemeleri ve geleneksel veritabanı katmanı yerine model için framework kullanılır.

Yapısı açısından, oldukça basittir:

- Ayarlar **constants.php** dosyasında saklanır 
- .htaccess tüm trafiği **controller.php** dosyası üzerinden iter
- Her rota **/components/pages/controllers** içinde bir denetleyiciye sahiptir ve herhangi bir mantık uyguladıktan sonra denetçi, **/components/pages/views**'den bir mustache şablonu görünümünü çağırır
- Ana sayfa UI, **/ui/default** daki mustache şablonlarında saklanır **

Admin uygulaması NAKİT İstek/Cevap tiplerini yansıtacak şekilde yapılandırılmıştır ve kaybedilmemelidir - Bu aynı dili konuşan müzisyenleri ve geliştiricileri elde etmek gibi bir çok amaca hizmet eder.
