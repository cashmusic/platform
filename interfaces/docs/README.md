# Ang CASH Music na Plataporma #
Sa puso ng CASH Music na plataporma ay isang transakyunal na balangkas na binuo 
para sa pagtatampok ng mga musika, pagbebenta, at digital na distribusyon. Naisali namin ang 
mga bagay tulad ng access sa datos, imbakan ng asset, at pangatlong partido na pamamahala ng API management na binibigyan ang
mga tagabuo at mga panghuling tagagamit ng malinis at matalinong mga workflow na sumasalamin sa
bawat isa. 

Para sa mga panghuling tagagamit, nagtatayo kami ng nakakapag-isang administrasyon na webapp na nakikipag-interak sa
balangkas upang payagan ang access na walang code sa lahat ng functionality ng CASH na
plataporma. Ang isang simple at ligtas na login, ilang mga web na form, at gabay na tulong sa bawat
pahina ay kabuuan ng namamagitan sa isang taong naghahanap na magdagdag ng bagong functionality sa
kanilang sayt. Kahit ang proseso sa pag-embed ay kasindali ng pagkopya ng code sa WordPress,
o pagputol at paglagay ng dalawang simpleng linya ng PHP. 

Ang mga tagabuo ay sumusunod sa isang pamilyar na Kahilingan/Kasagutan na pattern, na nakikipag-interak sa lokal na 
Kahilingang bagay na hindi masyadong namodelo sa mga RESTful na pattern. Ang mataas na antas na mga workflow
ay tinutukoy bilang "Elements" sa balangkas, at pinapayagan ang access sa komplikadong mga transaksyon
gamit ang isang simpleng kahilingan. Ang bawat elemento ay naglalaman ng isang serye ng mga pinong kahilingan
na ginawa mula sa konsepto ng abstraksyon na tinatawag na "Plants." Katulad sa isang pabrika/trabahante na
pattern, ang mga Plant ay naglilikha ng mga functionality sa pamamagitan ng mga bagay na tinatawag na "Seeds," 
na naglalaman ng tiyak na functionality upang ma-access ang pangatlong partido na mga serbisyo, tiyak na mga 
library, atbp. 

Ang lahat ng mga konsepto sa balangkas ay hinahati-hati sa mga kategoryang:

- Sistema
- Mga elemento
- Mga asset
- Mga tao
- Komersyo
- Kalendaryo

Iyon ang anim na mga planta na matatagpuan sa balangkas, at nirerepresenta nila ang spectrum
ng CASHRequest na mga tawag. Sa katulad na paraan, ang panghuling tagagamit ay pinakikitaan ng isang menu na naglalaman ng:
Mga Elemento, Mga Asset, Mga Tao, Komersyo at Kalendaryo — na may kakayahang baguhin ang ilang
sistemang-malawakan na mga setting, detalye ng account, atbp. 

Ang mga istrakturang ito ay sadyang nagsasalamin sa isa't isa, at pinapahintulutan ang mga makata at panghuling tagagamit na
magsalita ng kaparehong wika ng mga tagabuo. Sa parehong pagkakataon, may isang amble flexibility
sa balangkas upang payagan ang malalimang pagbabago, madaling pagtitiyak sa pangatlong partido na API, 
bago at mga karaniwang elemento, atbp. 


BAGONG ISTRAKTURA:

I.		Introduksyon
			a. Setup
			b. Mga Code na Istandard
II.		PHP Core
			a. Kahilingan/Kasagutan na pormat
			b. Modelo ng pag-awtorisa
			c. Mga Kahilingan
III. 		API
			a. Verbose na API
			b. RESTful na API
IV.		Mga Elemento
			a. Istraktura at pagko-code
			b. Pag-e-embed
V.		Mga Koneksyon
VI.		Admin
