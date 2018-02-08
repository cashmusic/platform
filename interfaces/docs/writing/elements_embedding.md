Ginagamit namin ang (munting) karaniwang javascript na library, [cashmusic.js](http://cashmusic.github.io/cashmusic.js/), 
upang lumikha ng mga iframe embed para sa mga elemento. Pwede silang i-istilo upang matumbasan ang kahit anong sayt na may buong kontrol ng tagagamit
sa CSS at markup. Ang pag-e-embed ay direktahan at nangyayari sa isang kopya at paste code.

Sa isang simpleng halimbawa, ang isang elemento ay may naka-embed na id lamang:

<script src="https://gist.github.com/jessevondoom/5856499.js"></script>

Ang endpoint at id ay palaging kinakailangan, pero pwede mo ring piliin na magpapakita ang isang elemento 
na naka-overlay (naka-lightbox.) Ang isang naka-lightbox na elemento ay maglilikha ng isang link sa linya 
ng kapsyon na ipinasa sa window.cashmusic.embed na function. Pwede ka ring 
pumasa sa isang bagay na tinutukoy ang sukat at posisyon ng elemento sa loob ng overlay. 

Para sa embed na mga tawag pagkatapos ng pag-load ng pahina, maglaan ng isang target na elemento bilang huling argumento sa
window.cashmusic.embed. Ilalagay nito ang embed, iframe o lightbox na link, sa loob
ng unang tumugmang elemento. Ang target ay dapat isang string na gumagana sa
document.querySelector, katulad ng "#id", "#id .class", o kapareho nito.

Sa pag-iistilo, lahat ng mga iframe embed ay inilalagay sa isang &lt;div&gt; na naklase kasama ang "cashmusic embed" 
at ang naka-lightbox na mga embed link ay inilalagay sa isang &lt;span&gt; na naklase sa "cashmusic embed".

Isang halimbawa kasama ang lahat ng mga opsyon:

<script src="https://gist.github.com/jessevondoom/5860605.js"></script>

Nagtatrabaho rin kami sa isang JSON na object nakabase sa embed call. Kadalasan ito ay para sa malinaw na
formatting, pero mapapansin mo ang isang bagong CSS override na opsyon na hindi gumagana  sa
istandard na pamamaraan. Mas marami pa sa ganyan sa hinaharap...

<script src="https://gist.github.com/jessevondoom/ccfb4f71f7a905d82470.js"></script>
