Isa sa aming mga layunin ay ang patakbuhin ito sa pinakamaraming posibleng mga lugar, kaya pinagtatrabahuan namin ito
nang maigi upang panatilihin na kaunti lamang ang mga kinakailangan:

 * PHP 5.4+
 * PDO (isang default) at MySQL OR SQLite
 * mod_rewrite (para sa admin na app)
 * fopen na mga wrapper OR cURL

Para sa lokal na pagsusuri at paglilinang, ang mga kailangan mo upang makapagsimula ay
[VirtualBox](https://www.virtualbox.org/wiki/Downloads),
[Vagrant 1.4+](http://www.vagrantup.com/downloads.html), at ang repo na ito. I-fork lang, i-install ang
VirtualBox and Vagrant, pagkatapos ay buksan ang isang terminal na window at magtype sa direktoryo ng repo ng:

```
vagrant up
```  

Pupukawin ng Vagrant ang isang VM, mag-set up ng Apache, i-install ang plataporma, at simulang maglingkod ng isang
espesyal na dev websayt na may mga kasangkapan, mga dokumento, at isang buhay na instance ng plataporma — lahat nakamapa
sa **http://localhost:8888**.

![Ang dev na sayt ay isinali sa repo](https://static-cashmusic.netdna-ssl.com/www/img/platform/v9.png)

Kung gusto mong lumagpas sa simpleng setup na nilalaman ng aming mga vagrant na script, kakailanganin mong
baguhin ang **/framework/settings/cashmusic.ini.php** na file. Isinama namin ang isang template
(cashmusic_template.ini.php) at ang mga setting ay talagang direktahan. Pwede mong baguhin ang
mga setting ng database, palitan ang default ang sistemang salt para password na seguridad, i-set timezone at
mga email na setting, at lumipat sa pagitan ng isahan o maramihang-tagagamit na modo.
