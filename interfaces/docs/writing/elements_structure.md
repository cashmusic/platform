Ang isang elemento ay isang bundle lahat sa kanyang sarili na may tatlong mga bahagi:

  1. Isang **app.json** na paglalarawang file na tinutukoy ang maga elementong mensahe at datos na iiponin
  2. Isang Class na file na may mga lohika at mga estado para sa elemento
  3. Isang folder ng mga mustache template (mga markup view para sa bawat estado)

Ang mga datos at setting para sa bawat instance ng isang elemento ay nakaimbak nilang na-encrypt na JSON sa database, 
at inilalarawan sa **app.json** na file ng elemento. Ang app.json na file ay naglalarawan sa lahat ng mga detalye tulad ng titulo
at deskripsyon ng elemento, mga instruksyon, at istraktura ng data kasama ang mga lebel, mensahe,
atbp. Ang isang detalyadong halimbawang file ay mas mabuti sa isang deskripsyon dito:

<script src="https://gist.github.com/jessevondoom/f471efa218a7ce78fa13.js"></script>

Ang mga opsyon ay naglalarawan sa kung ano ang ma-set sa elementong admin, anong mga opsyon ang naroon at 
inaasahan, at magbigay ng default na mga halaga, atbp. 

Ang mga pinapayagang uri ng opsyon ay:
  
  - pumili
  - mga halaga (kailangan)
  - boolean
  - numero
  - teksto
  - markup
  
At ang bawat opsyon ay naglalaman ng:

  - kinakailaangan
  - default
  - displaysize
  - helptext
  - placeholder


#### Ang pangunahing elementong class
Ang pangunahing elementong class na nagpapahaba sa ElementBase na class, na pinapadali ang karamihan sa mga 
pamamahala ng mga estado at pagpili ng template na kailangan mo. Ang lohika ay maaaring maging komplikado kung kinakailangan,  
pero sa katapusan, ang layunin ay ilarawan ang mga datos sa**$this->element_data** at ibalik 
ito bilang output ng getData() na function. Ano man ang inilalarawan mo ay maidadagdag sa  
nakaimbak na datos at maa-access sa iyong mga mustache template.

Sa halimbawa sa ibaba, tandaan na ginagamit natin ang **$this->setTemplate()** na function sa pagpili
sa isang template na hindi ang (kinakailangang) default.mustache na file. Ito ay nakabase sa mga filename sa
template na folder, at kinokontrol ng estado — ibinalik ng CASH Response UID ang huling
hiling. Kaya kapag ang isang elemento ay naglalaman ng isang form na nagpapagana sa isang CASH Request gamit ang GET o POST
the element responds, sets its internal state, and your getData() function does some magic
base sa estado bago ibinablik ang kinakailangang datos sa pag-render ng iyong embed.

Sa paghihiwalay ng getData mula sa rendering output, pinapahintulutan natin ang mga elemento na gumana sa lahat ng mga estado sa
isang malinaw na antas ng datos — hinayaan ang room sa hinaharap para sa suporta sa mobile app, atbp. Pero sa ngayon,
ang datos is ay sinasama sa mustache template na napili mong i-render sa HTML sa browser. 

<script src="https://gist.github.com/jessevondoom/39eaf1bb6fb84b5c1cd9.js"></script>

Simula pa lang ito. Sa marami pang mga halimbawa, tingnan ang **/framework/classes/elements**
na direktoryo sa pangunahing repo ng plataporma.
