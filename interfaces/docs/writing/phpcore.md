Ang lahat ng functionality ng plataporma ay maa-access sa pamamagitan ng konsistent na modelo ng kahilingan/kasagutan sa
puso ng PHP Core. Walang direktang mga function na tawag ang dapat gawin — sa halip ang datos ay dapat i-access
at itakda sa pamamagitan ng isang ligtas at istandard na modelo ng kahilingan/kasagutan.

The request/response model lets us build consistency from PHP to API and into elements and 
connections. It mimics a REST-style API and standardizes calls and responses across the methods.

Ang bawat kahilingan ay ginawa na may kasamang tiyak na uri at aksyon, at saka kahit anong kinakailangan o opsyonal 
na mga parametro. Ang sagot nito ay maglalaman ng isang http-esque **status code**, isang **uid** na naglalaman ng 
type/action/code, isang nababasang **status message**, isang mas detalyadong **contextual message**, 
isang echo ng **request type** at **action**, isang **payload** na may kasamang buong datos ng kasagutan 
o hindi totoo kapag pumalpak ang kahilingan, isang **api version**, at isang **timestamp**.

Ang Pagsisimula ng Kahilingang PHP ay nagmumukhang ganito: 

<script src="https://gist.github.com/jessevondoom/1b8cb605f999bd8ecadd.js"></script>

Isang halimbawa ng bagay ng pumalpak na kasagutan:

<script src="https://gist.github.com/jessevondoom/b8f3c7ba595c7ff3f861.js"></script>

O sa tagumpay:

<script src="https://gist.github.com/jessevondoom/280ded1684f165e94c85.js"></script>

Ang payload ay ibinabalik bilang isang naisasamang hanay. Karamihan sa mga simpleng kahilingan ng datos ay nagsasangkot ng 
paglilikha at pagbabagong mga petsa na istandard at awtomatiko sa sistema. Ang mga kahilingang
lumikha ng mga bagong kagamitan ay magbabalik ng numero ng id kapag nagtagumpay. 

Ang lahat ng mga core na file ay matatagpuan sa repo sa **/framework/classes/core** kasama ang mga kahilingang hinahati-hati
sa uri at inaayos sa isahang mga klase ng planta sa **/framework/classes/plants**. Karamihan sa
bagong functionality ay itinatakda sa plantang antas, kung saan ang mga klase ng core ay ginamit upang subaybayan ang mga kahilingan, 
sa mga planta, mga abstrak na koneksyon sa database, atbp.

Ang bawat planta ay naglalaman ng isang talahanayan para sa mga kahilingan na tumutukoy sa mga panloob na function at inilalarawan
ang kontekstong awtentikasyon sa ilalim ng kung saan sila pinapahintulutan. Magtingin sa ibaba para sa kompletong listahan ng 
mga kahilingan na inilabas ng core.
