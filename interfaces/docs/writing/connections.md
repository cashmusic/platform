Ang mga koneksyon ay mga API wrapper — isang natatagong paraan upang makakonekta tayo sa pangatlong
partido na mga serbisyo, pagsama-samahin ang mga inaalok, at tukuyin ang mga datos na dapat iponin upang gawin
ang trabaho ng bawat isa. (Mga token, setting, atbp.)

Ang paglalarawan sa koneksyon ay nangyayari sa dalawang magkaibang mga file:

 1. Isang definition JSON sa **/framework/settings/connections**
 2. Isang Seed Class sa **/framework/classes/seeds**

Ang koneksyon ay tinutukoy sa JSON at dapat mukhang ganito:

 <script src="https://gist.github.com/jessevondoom/2908c44b88db934aeec5.js"></script>

Kailangan nito ng pangalan, deskripsyon, natatanging uri, at dapat ilarawan mo ang classname ng seed na
ginamit sa koneksyon. May isang pagpipilian para sa saklaw. Pinapayagan tayong pakitirin ang uri ng koneksyon
upang maipapakita natin ang koneksyon sa kontekstwal na paraan sa admin app at lagpas pa. (Kasalukuyan, ang mga
uri ng saklaw ay patas na napipili, kahit na kailangang tukuyin natin sila them na mas maayos na kumukilos
paunahan.) Sa huli, may isang listahan ng listing compatibility — ginagawa ang trabahong koneksyon sa 
single-user mode, multi-user mode, o dalawa?

Tandaan na pwede mong ilarawan ang iba't ibang mga datos na kinakailangan para sa bersyon iisa at maramihan ang gumagamit.
sa koneksyon. (Hindi namin gusto ang pagtatago ng mga key/secret sa multi-user mode.)

####  Ang Seed class
Sa mas malaking skema ng plataporma, ang mga planta ay naghahawak sa mga hiling habang ang mga seed ay humahawak sa tiyak na
functionality — karamihan sa anyo ng pagkonekta sa pangatlong partido na mga API. Dahil ang mga seed ay
inaasahang maging flexible, talagang arbitraryo sila sa istraktura, pero makakapansin ka ng maraming
mga karaniwang gamit sa pagitan ng magkakaparehong mga seed — tingnan ang mga seed para sa S3 at Google Drive. 

Ilalarawan natin ito sa mas konkretong paraan sa isang saklaw-saklaw na basehan, pero sa ngayon, mangyaring 
maghanap ng kaparehong mga serbisyo at pattern pagkatapos sa kanila. Gusto namin ng mas maraming pagkakapareho 
sa antas ng seed upang makakaintindi tayo ng lubos mula sa antas ng planta.

Ang kahit anong koneksyong sumusuporta sa OAuth style redirect (kadalasan sa multi-user na mode) ay mangangailangang
ang dalawang function na ito ay natiyak:

 - getRedirectMarkup($data=false)
 - handleRedirectReturn($data=false)

Ang getRedirectMarkup ay maghahawak sa kahit anong lohikang kailangan upang makapaghayag sa sa gumagamit ng isang redirect na 
link, at ang handleRedirectReturn ay magsasaklaw sa mga binabalik na mga token at kompletohin ang 
proseso ng koneksyon.

Ang kahit anong datos tulad ng application-level na mga key na kailangang imbakin upang simulan ang mga OAuth requests 
ay pwedeng iponin sa **/framework/settings/connections.json**. Tingnan ang template sa
/framework/settings/_connections.json for a quick example.
