Ang aming API ay isang nag-iisang app na makikita sa repo sa **/interfaces/api/** at nasa
/api/ na URL sa cashmusic.org at lokal na para sa pagsusuri. Katulad ng admin app, lahat ng 
konpigurasyon ay hinawakan ng constants.php na file na makikita sa app root, at lahat ng mga 
hiling ay nakaruta sa controller.php na script gamit ang mod_rewrite. 

Ang pangunahing API na functionality ay hinahawakan ng **APICore** na klase, na matatagpuan sa
**/interfaces/api/classes/APICore.php**.

Ibinibalik ng API ang mga JSON object para sa kahit anong hiling. Ang simpleng paghiling ng **/** sa **/api**
ay nagbibigay ng simpleng pagbati na may kasamang numero ng bersyon:

<script src="https://gist.github.com/jessevondoom/b51b3ec5bee653d46cff.js"></script>

Ang hindi mabuting hiling ay nagbabalik ng mas istandard na format na may kasamang status code at mga mensahe:

<script src="https://gist.github.com/jessevondoom/c01eaae218cb6129acbf.js"></script>

Idedetalye namin ang mga matagumpay na mga hiling sa detalyado at RESTful na mga dokumento sa ibaba.
