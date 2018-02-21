Elements, CASH Music platformunda eşsiz iş akışları paketliyor. Bunları, telefonlardaki API'lere erişen uygulamalar gibi çekirdeğe erişen uygulamalar olarak düşünün. Kullanımı ve yapılandırması kolay olan basit bir paket oluşturma fikri için uygulama mağazalarından ilham aldık.

Her öğe, belirlenmiş bir deseni izleyen bir PHP sınıfı, işaretleme için bıyık şablonları, küçük resim için bir görüntü, bir LİSANS ve beraberinde bir JSON tanım dosyası içerir. Gerisi otomatik olarak işlenir. Tüm ayar formları yönetici uygulaması tarafından oluşturulur (ve iserseniz PHP'den manuel olarak bile ayarlanabilir.)

Ana öğe sınıfı, çeşitli dereceleri tanımlar ve yanıtlar; genellikle GET veya gömme yoluyla POST isteği ile tetiklenir. Öğe kendi kimliğini dinler ve eşleşen tüm NAKİT Tepkilerine tepki verir.

Bir öğe tek bir işlev çağrısı ile gömülür ve etkileşime girildiğinde otomatik olarak yanıt verir. Bütün fikir, PHP çekirdeğinde yenilik yapacak kadar güçlü, esnek ve kullanımı kolay bir yapıda olmasıdır.
