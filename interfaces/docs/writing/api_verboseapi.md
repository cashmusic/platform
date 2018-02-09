Ang verbose na API ay isang direktang wrapper para sa lahat ng mga kahilingan na pinahihintulutan ang api_public o api_key 
na mga pamamaraan sa pag-access. Sa ngayon, sila ay kadalasang limitado sa paglilista ng mga signup at pagsisimula ng mga bagong
transaksyon, pero pinagtatrabahuan namin ang isang buong skema ng awtorisasyon upang mapalawak ang saklaw.

Ang kasagutang object at payload ay halos kapareho sa ibinabalik mula sa PHP core, 
maliban ang na ibinalik bilang JSON. Ang paggawa ng isang kahilingan na hindi nagbibigay ng API na access ay magbibigay sa iyo ng isang
pinagbawalang estado, pero narito ang isang pangkatapusang halimbawa:

	/api/verbose/asset/getasset/id/2

Ang format ay simple lang: /verbose/**plant**/**request**/**{parameter name}**/**{parameter value}**
 — magkukuha ng ito ng maraming mga parametro na itinatapon mo dito at nagsasagot ng:

<script src="https://gist.github.com/jessevondoom/a3d384453bf053a2ca8e.js"></script>

May marami pang mga pamamaraan sa awtorisasyon na malapit nang dumating.
