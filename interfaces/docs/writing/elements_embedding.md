Öğeler için iframe gömme oluşturmak için (küçük) özel javascript kitaplığımızı [cashmusic.js](http://cashmusic.github.io/cashmusic.js/)
CSS ve biçimlendirme üzerinde tam kullanıcı denetimi olan herhangi bir siteyle eşleşecek şekilde tasarlanabilirler. Gömme işlemi oldukça basittir ve tek bir kopyalama ve yapıştırma kodu ile olur.

Basit bir örnek vermek gerekirse, bir öğe yalnızca kimliği vasıtasıyla yerleştirilir:

<script src="https://gist.github.com/jessevondoom/5856499.js"></script>

Bitiş noktası ve kimliği her zaman gereklidir, ancak öğenin bir katman ile (aydınlatılmış katman) görünmesini de seçebilirsiniz. Aydınlatılmış öğe, window.cashmusic.embed işlevine iletilen başlık ile satır içi bir bağlantı oluşturacaktır. Ayrıca, aydınlatılmış katman içindeki öğenin boyutunu ve konumunu belirten bir nesneye de geçebilirsiniz.

Sayfa yüklemesinden sonra gömme çağrıları için window.cashmusic.embed'in son argümanı olarak bir hedef öğe temin edin. Bu, ilk eşleşen öğenin içine gömme, iframe veya aydınlatılmış katman bağlantısı yerleştirir. Hedef, 'id', 'id .class' veya benzeri, document.querySelector ile çalışacak bir dize olmalıdır.
