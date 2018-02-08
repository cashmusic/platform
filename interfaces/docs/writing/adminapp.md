Ang admin app para sa plataporma na (**/interfaces/admin**) ay isang direktang MVC na istilong webapp 
na ginawa kasama ang isang front controller, isa-isang individual na kontroller para sa bawat ruta, mga mustache view, at gamit ang 
balangkas para sa modelo sa halip na tradisyonal na layer ng database. Sa madaling salita, isa itong dog-fooding sa PHP 
na core pero nagtatayo ng mas komplikadong app kaysa isang simpleng elemento. 

Sa batayang pangbalangkas, simple lamang ito: 

 - Ang mga setting ay inipon sa **constants.php** na file
 - Ang .htaccess ay nagpipilit sa lahat ng trapik sa pamamagitan ng **controller.php** na file
 - Ang bawat ruta ay may controller sa **/components/pages/controllers** at pagkatapos gumawa ng kahit anong lohika, 
tinawag ng controller ang isang mustache template view mula sa **/components/pages/views**
 - Ang pangunahing pahina ng UI ay nakaimbak sa mga mustache template sa **/ui/default**

Hindi dapat mawala na ang admin na app ay nakabalangkas upang masasalamin ang mga uri ng CASH Request/Response — ito ay 
talagang nasa misyong magsalita ang mga musikero at tagabuo ng iisang wika.
