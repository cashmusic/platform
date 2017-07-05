<?php

namespace CASHMusic\Seeds;

use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHQueue;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\SeedBase;

class ExternalFulfillmentSeed extends SeedBase
{
    public $user_id, $system_job_id, $fulfillment_job, $job_name, $status, $queue;
    private $uploaded_files, $raw_data, $parsed_data, $mappable_fields, $mapped_fields, $minimum_field_requirements;

    public function __construct($user_id=false)
    {

        $this->raw_data = [];
        $this->parsed_data = [];
        $this->mappable_fields = [];
        $this->has_minimal_mappable_fields = false;

/*        // default for kickstarter imports
        $this->mapped_fields = [
            'name' => 'Shipping Name',
            'email' => 'Email',
            'price' => 'Pledge Amount',
            'notes' => 'Notes',
            'shipping_address_1' => 'Shipping Address 1',
            'shipping_address_2' => 'Shipping Address 2',
            'shipping_city' => 'Shipping City',
            'shipping_province' => 'Shipping State',
            'shipping_postal' => 'Shipping Postal',
            'shipping_country' => 'Shipping Country'
        ];*/

        // dumbshit
        $this->mapped_fields = [
            'name' => 'Backer Name',
            'email' => 'Email',
            'price' => 'Pledge Amount',
            'notes' => 'Notes',
            'shipping_address_1' => 'Shipping Address 1',
            'shipping_address_2' => 'Shipping Address 2',
            'shipping_city' => 'Shipping City',
            'shipping_province' => 'Shipping State',
            'shipping_postal' => 'Shipping Postal Code',
            'shipping_country' => 'Shipping Country Code'
        ];

        $this->minimum_field_requirements = [
            'name' => false,
            'email' => false
        ];

        $this->user_id = $user_id;

        if (!$this->db) $this->connectDB();
    }

    public function getUserJobs()
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ]/*,
            'status' => [
                'condition' => '=',
                'value' => 'processed'
            ]*/
        ];

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $conditions, false, 'id DESC'
        )
        ) {
            return false;
        } else {

            $user_jobs = [];

            // loop through each job found
            foreach ($fulfillment_job as $job) {
                $tiers = $this->getTiersByJobCount($job['id']);

                if ($tiers < 1) {
                    $tiers = false;
                }
                $job['tiers_count'] = $tiers;

                $user_jobs[] = $job;
            }

            return $user_jobs;
        }
    }

    public function getUserJobById($id)
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'id' => [
                'condition' => '=',
                'value' => $id
            ]
        ];

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $conditions
        )
        ) {
            return false;
        } else {

            $user_jobs = [];

            // loop through each job found
            foreach ($fulfillment_job as $job) {
                $tiers = $this->getTiersByJob($job['id']);

                if ($tiers < 1) {
                    $tiers = false;
                }

                $job['tiers'] = $tiers;
                $job['tiers_count'] = count($tiers);

                $user_jobs[] = $job;
            }


            // fpo only
            if (isset($user_jobs[0]['tiers'][0])) {
                $tier_id = $user_jobs[0]['tiers'][0]['id'];

                $emails = ["michelle.alfardan@gmail.com",
                    "rmdarnell@hotmail.com",
                    "cky.taylor@gmail.com",
                    "kooricyclone1@gmail.com",
                    "matthew-dean@live.com",
                    "stownsend80@gmail.com",
                    "coulevious99@live.com",
                    "egan.rebeccalorraine@live.com.au",
                    "filoguy2009@gmail.com",
                    "Jamroseveronica@gmail.com",
                    "lianalopez@iinet.net.au",
                    "rossabarquez@yahoo.com",
                    "chris_beckham_2k@hotmail.com",
                    "markrbernard_@hotmail.com",
                    "rae_p_@hotmail.com",
                    "alcs82@msn.com",
                    "johnsoulos@yahoo.com.au",
                    "dear_lils@yahoo.com.au",
                    "buckleup1@optusnet.com.au",
                    "ghostforbreakfast@gmail.com",
                    "tyshardy.nineteen97@live.com.au",
                    "nathan.s.cornwall@gmail.com",
                    "trentripper@gmail.com",
                    "nadiafongaro@optusnet.com.au",
                    "sophia-l-2011@hotmail.com",
                    "djfarry1@bigpond.net.au",
                    "tinalundon@gmail.com",
                    "irene@celestiallight.com.au",
                    "anthony.jakob@gmail.com",
                    "phatbass82@hotmail.com",
                    "mori.perez@gmail.com",
                    "nagromone@ozemail.com.au",
                    "sirfinn@aapt.net.au",
                    "heracy916@gmail.com",
                    "gamepsychology@gmail.com",
                    "maniatis.e@gmail.com",
                    "wild_boy888@hotmail.com",
                    "trebleclefrk@hotmail.com",
                    "scottjs@gmail.com",
                    "james.siemon@gmail.com",
                    "hasa81@gmail.com",
                    "stash019c@hotmail.com",
                    "isabell.alnahat@gmail.com",
                    "adamnissen@gmail.com",
                    "2603hodge@gmail.com",
                    "shanteldacosta@hotmail.com",
                    "blitza.poulter@gmail.com",
                    "riccardo.raiti@gmail.com",
                    "sean.brown93@hotmail.com",
                    "angeepangeed@msn.com",
                    "sparvogamarie@yahoo.com",
                    "hot_torana_chick@hotmail.com",
                    "1wayjesus777@gmail.com",
                    "danz_ricrur@hotmail.com",
                    "celiciavs@gmail.com",
                    "charlene.mrsfox@yahoo.com.au",
                    "plucka1984@gmail.com",
                    "hp.register@gmail.com",
                    "lorenzodafre@hotmail.com",
                    "brownjeff20@aol.com",
                    "mrsblcampbell@gmail.com",
                    "iflysm@live.ca",
                    "h.szeto@gmail.com",
                    "myra.villaflor@gmail.com",
                    "mmatthews-2@hotmail.com",
                    "stacey_bourgeois@hotmail.com",
                    "altophobia@hotmail.com",
                    "kelyn3@gmail.com",
                    "elvick_rules_all@yahoo.com",
                    "mmaynard@eastlink.ca",
                    "farleylau@hotmail.com",
                    "officialnjtaylor@gmail.com",
                    "steve.ezekwem@gmail.com",
                    "boitedesix@gmail.com",
                    "dtown_boy@hotmail.com",
                    "dileomichele270@gmail.com",
                    "tree.samuels@gmail.com",
                    "RICH.LEYTE@GMAIL.COM",
                    "lmozn1814@gmail.com",
                    "michiegarcia@gmail.com",
                    "azel.siapno@gmail.com",
                    "pcdorney@hotmail.com",
                    "nawkturnal@gmail.com",
                    "guy_xtc@hotmail.com",
                    "cm_baillie@yahoo.com",
                    "northrupkevin@gmail.com",
                    "ohbrandon@me.com",
                    "waliar64@gmail.com",
                    "lazyeyemcfly@gmail.com",
                    "thenamesmay@gmail.com",
                    "bergdj67@gmail.com",
                    "vdateacha@gmail.com",
                    "martin.falardeau@gmail.com",
                    "kyleorca@gmail.com",
                    "inariangel@gmail.com",
                    "rola.brikho@gmail.com",
                    "shirazx5@gmail.com",
                    "quiquaequod5@gmail.com",
                    "superstar0120@hotmail.com",
                    "remykarda@hotmail.com",
                    "aktolentino@hotmail.com",
                    "starkravenmad@gmail.com",
                    "jayde.wilburg@gmail.com",
                    "eminem_freak20@hotmail.com",
                    "jtransam@rogers.com",
                    "ahrynchuk@outlook.com",
                    "mcfield78@hotmail.com",
                    "michael.sacher@gmail.com",
                    "fredrich@t-online.de",
                    "kickstarter@carstenberg.net",
                    "matthew.taylor@hotmail.de",
                    "rene@timely.de",
                    "kickstarter@favo.org",
                    "dfblaser@gmail.com",
                    "floriangoetz90@gmail.com",
                    "erikajane@gmail.com",
                    "aarslan@live.de",
                    "mihael.matijasevic@gmail.com",
                    "mie.svane@gmail.com",
                    "Blessedscalp@gmail.com",
                    "rs1162@gmail.com",
                    "ruben.fdez.m@hotmail.com",
                    "snakers10@hotmail.com",
                    "agustingcascales@gmail.com",
                    "nondedeu@hotmail.com",
                    "siekensousie@hotmail.com",
                    "jmhulkkonen@gmail.com",
                    "tlc_fan@hotmail.fr",
                    "nancy.lessert@orange.fr",
                    "sarahfedermann@hotmail.com",
                    "assia.redjem@outlook.com",
                    "tiphaine.bressin@gmail.com",
                    "catseyes21april@yahoo.com",
                    "minettethecat@gmail.com",
                    "davidroos@live.fr",
                    "sbordier16@gmail.com",
                    "laurent@yhuel.fr",
                    "jtea@free.fr",
                    "kristellbat@hotmail.com",
                    "kickstarter@braunyakka.co.uk",
                    "p@trickshort.com",
                    "chip.yi@hotmail.com",
                    "shaunrtt@hotmail.com",
                    "AffirmationTheInventor@Gmail.com",
                    "keepitup50@hotmail.com",
                    "cara.g.williams@gmail.com",
                    "lateifahb@gmail.com",
                    "pussycatboi@hotmail.com",
                    "Queenpat@lineone.net",
                    "bryanhepburn@hotmail.co.uk",
                    "sarah.corfield@outlook.com",
                    "sarahlou_23@hotmail.com",
                    "leetamber@yahoo.com",
                    "muzicmaster15@yahoo.com",
                    "mail@sebrichter.com",
                    "jonhornbuckle@gmail.com",
                    "njjobbins@gmail.com",
                    "david.olshanetsky@icloud.com",
                    "syselcuk1@gmail.com",
                    "secretgarden1974@hotmail.co.uk",
                    "davidjamesgriffiths@hotmail.co.uk",
                    "iwallman@hotmail.com",
                    "pinkstarbuck@hotmail.co.uk",
                    "becki_keating@HotmAil.com",
                    "rozza_rozza@hotmail.co.uk",
                    "Laurence.Jenkins@gmail.com",
                    "prak93@hotmail.co.uk",
                    "pierre@lallier.co.uk",
                    "katycostello@hotmail.com",
                    "lefteye1971_2002@hotmail.com",
                    "andyowen84@googlemail.com",
                    "williams461@btinternet.com",
                    "bushra@gmail.com",
                    "averagewhitetac@aol.com",
                    "staceysimmonds@icloud.com",
                    "scanti@oppositespin.com",
                    "ernonewman@gmail.com",
                    "Arwallace@hotmail.com",
                    "steve_clarke100@hotmail.com",
                    "mpharris1975@gmail.com",
                    "kingg1st@hotmail.com",
                    "rowaz@hotmail.com",
                    "susie.bewell@btclick.com",
                    "becky7ellis@gmail.com",
                    "strange_gurl_no3@hotmail.com",
                    "ad1980@hotmail.com",
                    "soniagill27@hotmail.com",
                    "cabaniuk.marko@live.co.uk",
                    "nickw101@googlemail.com",
                    "limpade@yahoo.com",
                    "louiseshort247@hotmail.com",
                    "martinps@hotmail.com",
                    "stewkingwang@hotmail.com",
                    "nrmann@btinternet.com",
                    "rezeye99@yahoo.com",
                    "freakylindsay15@yahoo.co.uk",
                    "bbowe1962@gmail.com",
                    "f1rules02@yahoo.co.uk",
                    "emilyshipp7@gmail.com",
                    "lucy.ibberson@gmail.com",
                    "markgoddard@blueyonder.co.uk",
                    "ryanb3698@hotmail.co.uk",
                    "Originalrobski@gmail.com",
                    "jigh4eva@googlemail.com",
                    "will8am@gmail.com",
                    "cozzyrose@gmail.com",
                    "cullenfl@tcd.ie",
                    "frances_2283@outlook.com",
                    "jnozawa@yahoo.com",
                    "jasonyky@hotmail.com",
                    "teyop8@hotmail.com",
                    "CrypticMoonlight@gmail.com",
                    "nofar_efraim@hotmail.com",
                    "vigdislilja@simnet.is",
                    "delfrate@gmail.com",
                    "kanetsu.hebi@live.it",
                    "kenichi.uchiumi@gmail.com",
                    "azusa.nosaka@mms.bbiq.jp",
                    "makij16@hotmail.com",
                    "per4mnkzw.12@ezweb.ne.jp",
                    "simoon9@sky.dti2.ne.jp",
                    "jamology@gmail.com",
                    "letyourheadgo03@gmail.com",
                    "sweetmammy@i.softbank.jp",
                    "dic67@amail.plala.or.jp",
                    "masa.shopping@gmail.com",
                    "samlaw66@gmail.com",
                    "humi1471@gmail.com",
                    "mansanasmhk@yahoo.co.jp",
                    "mayu1211@gmail.com",
                    "moemoe666moemoe@gmail.com",
                    "ayun.sb3@gmail.com",
                    "moriizumii@gmail.com",
                    "rbshm471@yahoo.co.jp",
                    "juancmolinac@gmail.com",
                    "blonduiteenpotje@hotmail.com",
                    "nicolevillaseca@outlook.com",
                    "dyanex10@gmail.com",
                    "titiagerritzen@gmail.com",
                    "bjay_master@hotmail.com",
                    "musicfreak1105@yahoo.com",
                    "arne.scholte@icloud.com",
                    "andreachao@hotmail.com",
                    "manticon@planet.nl",
                    "jimmyo@xs4all.nl",
                    "gail_chan@hotmail.com",
                    "adrianastikic@gmail.com",
                    "twchao@me.com",
                    "even_td@hotmail.com",
                    "mwiggfb2@gmail.com",
                    "liquet87@hotmail.com",
                    "jankauskas.giedrius@yahoo.com",
                    "mandycrawley18@yahoo.com",
                    "schwate@yahoo.com",
                    "klal001@gmail.com",
                    "benmarc@gmail.com",
                    "gibbs7@wp.pl",
                    "samrobles2001@hotmail.com",
                    "13bandar@gmail.com",
                    "nale72@gmail.com",
                    "setlc@hotmail.com",
                    "gunnarhgberg@gmail.com",
                    "kurtctennant@gmail.com",
                    "boris_yeshin@yahoo.com",
                    "kwoksy81@yahoo.com",
                    "soriano.leo@gmail.com",
                    "aaronkec@yahoo.co.uk",
                    "kkwok@singnet.com.sg",
                    "niryn@hotmail.com",
                    "odie1111@gmail.com",
                    "ryjq@yahoo.com",
                    "iceholecyn@yahoo.com",
                    "sweetbabyangelbg@aol.com",
                    "pjgray4202@msn.com",
                    "marknakada@gmail.com",
                    "danaeliz1984@hotmail.com",
                    "Littleeye3d@comcast.net",
                    "magik88285@aol.com",
                    "g_redeemer@yahoo.com",
                    "poprockz212@yahoo.com",
                    "jackmarsac@gmail.com",
                    "keelo1027@yahoo.com",
                    "tmhardy7@yahoo.com",
                    "Lnnnnnnn@sbcglobal.net",
                    "otsiningopark@hotmail.com",
                    "musiclovetori@gmail.com",
                    "drkmker@live.com",
                    "briannan23@yahoo.com",
                    "lightabove@hotmail.com",
                    "rxg8or@yahoo.com",
                    "John@TheMarquettes.org",
                    "sjerez89@gmail.com",
                    "AntonylWilliamson@gmail.com",
                    "moybill@me.com",
                    "natcat104@yahoo.com",
                    "tmoneydx@gmail.com",
                    "mbain1989@gmail.com",
                    "triplejrocks@aol.com",
                    "ChristenKirby2010@gmail.com",
                    "averytinnermon@yahoo.com",
                    "shrlyndvs@aol.com",
                    "diackb@gmail.com",
                    "shor_ty_83@yahoo.com",
                    "jonpierremusic@gmail.com",
                    "kim_simmons91@yahoo.com",
                    "lawnservice.sm@gmail.com",
                    "venconmigo90@gmail.com",
                    "KennyO20123@gmail.com",
                    "nicole.wiktor@gmail.com",
                    "hellion.asm@gmail.com",
                    "phutmasterflex@yahoo.com",
                    "tellkenya@yahoo.com",
                    "skillz_20019@yahoo.com",
                    "sbutler00@comcast.net",
                    "sherriceherron@yahoo.com",
                    "sanchmon84@yahoo.com",
                    "nathanbohatch@gmail.com",
                    "smlillmars@comcast.net",
                    "Shavetta@gmail.com",
                    "juperlim@yahoo.com",
                    "confucious2001@yahoo.com",
                    "mbrevig@gmail.com",
                    "Carlos.Harleaux@gmail.com",
                    "jamesday047@gmail.com",
                    "enrique.chumbes@gmail.com",
                    "ryan.edward.price@gmail.com",
                    "bking113@yahoo.com",
                    "julius.viloria@gmail.com",
                    "teresalam0713@gmail.com",
                    "someoneelse@hotmail.com",
                    "wdray1989@yahoo.com",
                    "alvinblackmore@hotmail.com",
                    "interforcer@hotmail.com",
                    "hellopheobe2005@yahoo.com",
                    "paul.estrada2010@gmail.com",
                    "dminguit@gmail.com",
                    "w49ers16@yahoo.com",
                    "karinabeck@aol.com",
                    "Shirleylamoreaux@gmail.com",
                    "ollie.nash365@gmail.com",
                    "jaybrown222@gmail.com",
                    "MARIOMULDER65@HOTMAIL.COM",
                    "brianwboehm@gmail.com",
                    "zandy1@me.com",
                    "tyeesha.wesley@gmail.com",
                    "qbee72@yahoo.com",
                    "jimmyc.hsieh@gmail.com",
                    "icarnate@gmail.com",
                    "katzfantasy@aol.com",
                    "imkbrad@gmail.com",
                    "polydegmon@earthlink.net",
                    "michellec@epolycorp.com",
                    "mscharlee@gmail.com",
                    "skt302@gmail.com",
                    "veneziawhite@aim.com",
                    "derekst.mary@gmail.com",
                    "luraz333@aol.com",
                    "tlc_3d@Hotmail.com",
                    "bizzybigg@gmail.com",
                    "kblack25@gmail.com",
                    "dkalstud@yahoo.com",
                    "BtzSnc@Outlook.com",
                    "micheletlcfan@gmail.com",
                    "msmford@hotmail.com",
                    "koalagal@gmail.com",
                    "danielle_benson@hotmail.com",
                    "bryan.prado@gmail.com",
                    "christymoneyent@gmail.com",
                    "dicraig89@gmail.com",
                    "james.kona.iori@gmail.com",
                    "feliciabr68@gmail.com",
                    "Smfreeze@aol.com",
                    "gbrown@gtla.org",
                    "hellocheryl@gmail.com",
                    "rpsrose12@hotmail.com",
                    "yankeesprincess90@gmail.com",
                    "candacenelson11@gmail.com",
                    "ASumm005@gmail.com",
                    "beckymeverden@gmail.com",
                    "sateedrah@aol.com",
                    "romelowood@yahoo.com",
                    "meowllp@yahoo.com",
                    "stopjockinme23@yahoo.com",
                    "andreagraeser@gmail.com",
                    "jordan@jordancanfly.com",
                    "timo.pang@gmail.com",
                    "kupid860@gmail.com",
                    "elaine24@hotmail.com",
                    "danielle.daniels17@gmail.com",
                    "drrhwong@aol.com",
                    "Michael.Mercado@dignitymemorial.com",
                    "kathlenef@gmail.com",
                    "robshipp76@gmail.com",
                    "lavan.collins@gmail.com",
                    "tine_88@hotmail.com",
                    "mrtylr@yahoo.com",
                    "telaclay@hotmail.com",
                    "eljobi@gmail.com",
                    "jordanramsey@me.com",
                    "berchtoldbrian@yahoo.com",
                    "cdickson1985@yahoo.com",
                    "a_rocker92@yahoo.com",
                    "loveless1313@verizon.net",
                    "gkel69@yahoo.com",
                    "savonn.t@gmail.com",
                    "julienne.padojino@gmail.com",
                    "kooldmw@outlook.com",
                    "wanda.mac1@gmail.com",
                    "l.j.pinson@gmail.com",
                    "racheleschuck@gmail.com",
                    "Jaycee8526@yahoo.com",
                    "rhonda_peek@hotmail.com",
                    "nina_lemos@aol.com",
                    "rastachinky876@gmail.com",
                    "alonaturner@gmail.com",
                    "juliehedani@yahoo.com",
                    "mjthomas7980@att.net",
                    "MTCP187@YAHOO.COM",
                    "sgtechkitty@gmail.com",
                    "dolphincutii@yahoo.com",
                    "ltlbird2000@yahoo.com",
                    "rafirdy@aol.com",
                    "patcharinv@gmail.com",
                    "katherine.cachaper@gmail.com",
                    "obiaewah@gmail.com",
                    "gord168@aol.com",
                    "tan.johnr@gmail.com",
                    "dreid3@masonlive.gmu.edu",
                    "o.gotham@gmail.com",
                    "mmmquijano@sbcglobal.net",
                    "janetcampbell02@yahoo.com",
                    "the_pickle_weasel@hotmail.com",
                    "sdbrim@yahoo.com",
                    "rpinkgirlie@yahoo.com",
                    "scottish_usa@hotmail.com",
                    "johnnyb807@yahoo.com",
                    "klotzkelly@gmail.com",
                    "chinakechina@yahoo.com",
                    "chrishylton1@gmail.com",
                    "veganquinn@yahoo.com",
                    "jheinser@dons.usfca.edu",
                    "kj703@yahoo.com",
                    "samoangirl_91@yahoo.com",
                    "fullerj84@gmail.com",
                    "oopsimstronger@yahoo.com",
                    "supaflyguy@aol.com",
                    "angelica@garrison-martinez.com",
                    "diojoeboe@yahoo.com",
                    "kathyjoynes1221@gmail.com",
                    "oswaldomaxwell@gmail.com",
                    "bloombaby6789@gmail.com",
                    "mexitrucha@yahoo.com",
                    "miss_ava@yahoo.com",
                    "mike.andreozzi81@gmail.com",
                    "purplesqumpkie@gmail.com",
                    "lashawngee@gmail.com",
                    "brockamberjane@gmail.com",
                    "alycia924@yahoo.com",
                    "wobblewobblejs@yahoo.com",
                    "onslaughtxy@yahoo.com",
                    "seanmac46@gmail.com",
                    "dracaglacies@gmail.com",
                    "kittylo330@yahoo.com",
                    "texas.jaynes@att.net",
                    "cangelose.laura@gmail.com",
                    "kama.holder@gmail.com",
                    "mrdougie11@gmail.com",
                    "hgknutsen@yahoo.com",
                    "aunny@dlrpublicrelations.com",
                    "mamacita703@aol.com",
                    "prodjr19@yahoo.com",
                    "bcanty00@gmail.com",
                    "pandapawlin@gmail.com",
                    "mr.urruticochea@gmail.com",
                    "stevenlv@yahoo.com",
                    "wickedscents3@hotmail.com",
                    "mramos@sanbrunocable.com",
                    "shidoni@yahoo.com.ph",
                    "alicialeighalonso@gmail.com",
                    "beejay@cfl.rr.com",
                    "c.vitalien@gmail.com",
                    "erika5flores@gmail.com",
                    "linette422002@yahoo.com",
                    "hunterbrandon32@yahoo.com",
                    "ruesh_yazzie@yahoo.com",
                    "lindsayzelinski@gmail.com",
                    "anthony_hong@sbcglobal.net",
                    "boysvomitcandy@aol.com",
                    "xavierxstar@gmail.com",
                    "harringtonange@yahoo.com",
                    "robertoverton@gmail.com",
                    "mousepitt@yahoo.com",
                    "LoveStarDream3@aol.com",
                    "vsaldo@icloud.com",
                    "evcgrant@gmail.com",
                    "adamvalencia1@msn.com",
                    "funkymoon1@prodigy.net",
                    "plafcan21@gmail.com",
                    "mbishop@mail.com",
                    "mrand23@aol.com",
                    "stubblefieldgreta48@gmail.com",
                    "KellyDMartell@gmail.com",
                    "kpomares@gmail.com",
                    "kdiggs2@gmail.com",
                    "Derek_Vazquez@yahoo.com",
                    "carannsim@hotmail.com",
                    "alcamber@hotmail.com",
                    "jmarti1980@gmail.com",
                    "pixy9009@hotmail.com",
                    "prm450@gmail.com",
                    "cprimer22@yahoo.com",
                    "ianmalachai@gmail.com",
                    "rebekah.grace.ross@gmail.com",
                    "heth71084@gmail.com",
                    "kennipeach80@aol.com",
                    "scottandshannon9298@yahoo.com",
                    "cudeani@gmail.com",
                    "dandmalmeida@yahoo.com",
                    "laurayam@hawaii.edu",
                    "jazzi35@hotmail.com",
                    "shupercousin@hotmail.com",
                    "norahlally@gmail.com",
                    "afischer0911@gmail.com",
                    "Jennifer_515@msn.com",
                    "ollie313@yahoo.com",
                    "dontaibarnett@hotmail.com",
                    "ramonangelgrijalva@gmail.com",
                    "inmynismoz@aol.com",
                    "b.jonas32@gmail.com",
                    "spazzz2002@gmail.com",
                    "prentice.jamie@yahoo.com",
                    "ckahapea@hotmail.com",
                    "queeroid@yahoo.com",
                    "wrightdamon66@gmail.com",
                    "joannegeli@hotmail.com",
                    "yvaz2012@gmail.com",
                    "crystalclear0801@gmail.com",
                    "chalmers173@yahoo.com",
                    "choddy2010@aol.com",
                    "richardmelber@gmail.com",
                    "tazzzman78@gmail.com",
                    "nubiansis@sbcglobal.net",
                    "d.benvenuto@yahoo.com",
                    "lucas101@gmail.com",
                    "andrade.ae@gmail.com",
                    "m_a_cox@hotmail.com",
                    "lilgaries@yahoo.com",
                    "mclynn24@yahoo.com",
                    "obajaj1@yahoo.com",
                    "codyvfrost@gmail.com",
                    "verona1108@aol.com",
                    "misshayley603@yahoo.com",
                    "trishwinkle@yahoo.com",
                    "jordan.r.liberty@gmail.com",
                    "chrisccerami@gmail.com",
                    "fatal.diego.o.o@gmail.com",
                    "playboyfreshxxx@gmail.com",
                    "leslie.kon@gmail.com",
                    "jdaza23@gmail.com",
                    "chris.mcmasters@yahoo.com",
                    "jaylanell2010@gmail.com",
                    "moya36@hotmail.com",
                    "gindaejoong@gmail.com",
                    "chrisward47@msn.com",
                    "tmsloan1234@yahoo.com",
                    "chrislyn.mccloud@yahoo.com",
                    "vr5sbloom@aol.com",
                    "aja.russell23@aol.com",
                    "nikkles@juno.com",
                    "sodroadrunner@yahoo.com",
                    "kdill15@gmail.com",
                    "sonia.l.campos@gmail.com",
                    "soulsandsounds@yahoo.com",
                    "amanda_fritz80@hotmail.com",
                    "DanielleP34@hotmail.com",
                    "simplyrita29@gmail.com",
                    "camille.lorenzana@gmail.com",
                    "omarabul@hotmail.com",
                    "locksunlimited@bellsouth.net",
                    "flittle00@gmail.com",
                    "ss2beach@mac.com",
                    "pledges@kickstarter.com",
                    "dgc_32_2009@yahoo.com",
                    "melissa.harris04@me.com",
                    "charzrevmig86@gmail.com",
                    "ramiltonii@hotmail.com",
                    "ushersgirl19@yahoo.com",
                    "mkmccue@gmail.com",
                    "crystalcheng.cc@gmail.com",
                    "ronniebylow@gmail.com",
                    "akh1286@gmail.com",
                    "gavinh_express@hotmail.com",
                    "seunson2001@yahoo.com",
                    "t.mclaughlin@me.com",
                    "cru4liferkelleydirtysouth@yahoo.com",
                    "mertzc01@hotmail.com",
                    "ltcw218@hotmail.com",
                    "indymelissa71@gmail.com",
                    "gabrielg@satx.rr.com",
                    "402emartinez@gmail.com",
                    "justin_9205@yahoo.com",
                    "fredalmircal@yahoo.com",
                    "kb_miller85@yahoo.com",
                    "zettagurlxu@gmail.com",
                    "dsgina4@gmail.com",
                    "ry7904@aol.com",
                    "jay22246@gmail.com",
                    "monica@mtmrecognition.com",
                    "francisco.ortega2@gmail.com",
                    "redroo98@gmail.com",
                    "jandrocsuh04@yahoo.com",
                    "tybalt005@gmail.com",
                    "jasonrockwell79@yahoo.com",
                    "rgersy@me.com",
                    "nnew5066@yahoo.com",
                    "daniel.esannason@gmail.com",
                    "desannason@jd16.law.harvard.edu",
                    "kdcurry2@yahoo.com",
                    "gerikg@gmail.com",
                    "mdm1450@yahoo.com",
                    "e.silendt@gmail.com",
                    "escobeltx@yahoo.com",
                    "ameermiller@yahoo.com",
                    "smlpearman@yahoo.com",
                    "brithibodeaux@outlook.com",
                    "princeakili@mac.com",
                    "xeroxahippo@gmail.com",
                    "johndkellum3@gmail.com",
                    "striped_unicorn@yahoo.com",
                    "nbishop490@gmail.com",
                    "yotes_02@yahoo.com",
                    "missk.walsh@gmail.com",
                    "andrew.stearn@workingtitlefilms.com",
                    "coloradoisabel@yahoo.com",
                    "larrybob8311@sbcglobal.net",
                    "liberalpanther147@gmail.com",
                    "ariecker@gmail.com",
                    "ringram@gargoylecc.com",
                    "cathygumpert@gmail.com",
                    "otonashi@gmail.com",
                    "dizave7@yahoo.com",
                    "escape6682@yahoo.com",
                    "ladylonnie7@hotmail.com",
                    "justus@rocketmail.com",
                    "rpad1@outlook.com",
                    "kimhoneysett@yahoo.com",
                    "mikecasas1984@hotmail.com",
                    "dinkydink@hotmail.com",
                    "chamosaa@yahoo.com",
                    "ichibouka@gmail.com",
                    "benicetodan@yahoo.com",
                    "Sarahmosbacher@gmail.com",
                    "Jessicadusett@gmail.com",
                    "melanie.acosta007@gmail.com",
                    "arianahoneychurch@yahoo.com",
                    "leah.gonzer@gmail.com",
                    "cmstroschein@hotmail.com",
                    "e6prieto@gmail.com",
                    "rappsboyfrnd1026@aol.com",
                    "supernovalopes@gmail.com",
                    "karamelkitten@gmail.com",
                    "wildnat13@gmail.com",
                    "teresabeech@gmail.com",
                    "jamamb80@gmail.com",
                    "sreagles82@yahoo.com",
                    "nicolelouisethompson@gmail.com",
                    "middleton_charlene@yahoo.com",
                    "prgrrl7@yahoo.com",
                    "auninaettvesez@gmail.com",
                    "jwong@austincc.edu",
                    "guardian51483@aol.com",
                    "deedeevega84@gmail.com",
                    "kat.yalung@gmail.com",
                    "Ryumizaki@gmail.com",
                    "kdektas@hotmail.com",
                    "litlepro71@gmail.com",
                    "tcladylifer@gmail.com",
                    "eva0312pisces@gmail.com",
                    "ever_clear45@hotmail.com",
                    "joserios426@gmail.com",
                    "alexa.finley11@gmail.com",
                    "laurenj2913@yahoo.com",
                    "quietkat@pstel.net",
                    "Jennpgh@hotmail.com",
                    "virgo82670@yahoo.com",
                    "Jbogart@sas.upenn.edu",
                    "jazzilawson@yahoo.com",
                    "cjs7t8@gmail.com",
                    "mariadellajohnson@yahoo.com",
                    "bronxboi87@gmail.com",
                    "hdwilliams95@msn.com",
                    "wmylin88@gmail.com",
                    "jnm4eva3@yahoo.com",
                    "smirzainteriors@gmail.com",
                    "davidjr25@gmail.com",
                    "mphelps93@yahoo.com",
                    "kalanz@comcast.net",
                    "tommyha@gmail.com",
                    "kickinchickin67@aol.com",
                    "jlokensky@yahoo.com",
                    "lam_nancy@yahoo.com",
                    "epuente@alum.wellesley.edu",
                    "hannabear@gmail.com",
                    "mpasconeii@yahoo.com",
                    "vonmaack4@aol.com",
                    "I.a.j.820407@gmail.com",
                    "masakura@hotmail.com",
                    "triplethefield@gmail.com",
                    "LTpi438@gmail.com",
                    "lilahunter010@yahoo.com",
                    "superheroclix@hotmail.com",
                    "epartida@toppenish.wednet.edu",
                    "saphirblau0986@hotmail.com",
                    "t1klm3fatty@live.com",
                    "malikniles3@yahoo.com",
                    "ryanburgess@verizon.net",
                    "daplayboypimpett@yahoo.com",
                    "mrsjessicatrahan@gmail.com",
                    "icare4up2@yahoo.com",
                    "tyler545619@gmail.com",
                    "chrstnrobidoux@gmail.com",
                    "nickcucinotta@ymail.com",
                    "catlara@yahoo.com",
                    "libbie11782@gmail.com",
                    "kireiusagi32@aol.com",
                    "deshantam@yahoo.com",
                    "aawashingtonjr@hotmail.com",
                    "lkeezy@gmail.com",
                    "wsamuels68@gmail.com",
                    "dearwester@yahoo.com",
                    "iamharrynelson@gmail.com",
                    "liddell.stacey@yahoo.com",
                    "zac0322yo@gmail.com",
                    "kissedbyarose88@gmail.com",
                    "g_wells04@hotmail.com",
                    "carlcfoley@yahoo.com",
                    "fvallejo@gmail.com",
                    "seng.souk@gmail.com",
                    "shaunphillips86@gmail.com",
                    "sixmilepainting@yahoo.com",
                    "lydiaearle@hotmail.com",
                    "scenexcorelozl@gmail.com",
                    "aries8424@gmail.com",
                    "sabeattie@mac.com",
                    "midgeyb@hotmail.com",
                    "Kristin.jong@gmail.com",
                    "agustindamers@gmail.com",
                    "redneckfan4life@yahoo.com",
                    "loweryourgrade@gmail.com",
                    "Eardeliciousdiva@aol.com",
                    "brad@cmcaplan.com",
                    "huggins_30311@yahoo.com",
                    "cdegregorio1129@aol.com",
                    "DayDreamer@xe1media.com",
                    "jessical8r@aol.com",
                    "JPszczola87@aol.com",
                    "Shatongamc@gmail.com",
                    "dalisbethgalvez@gmail.com",
                    "itsignaciocobian@gmail.com",
                    "JennaMapp@msn.com",
                    "juan23letsgo@yahoo.com",
                    "ycsm2004@yahoo.com",
                    "mnc_shfrd@yahoo.com",
                    "BenjaminWensinger@gmail.com",
                    "senoja1@comcast.net",
                    "Najah6907@yahoo.com",
                    "r.nyc@aol.com",
                    "regina.larkin@gmail.com",
                    "vetmed11@gmail.com",
                    "skylarwilkins@gmail.com",
                    "ejpisme@gmail.com",
                    "crskid@hotmail.com",
                    "bweitz91@gmail.com",
                    "jennymeeganharsip@gmail.com",
                    "freekstyleborn@gmail.com",
                    "cbobo03@yahoo.com",
                    "dittles3917@yahoo.com",
                    "edwardjcoleman@yahoo.com",
                    "falloutrenthead@aol.com",
                    "cleothasims@gmail.com",
                    "jasminemr1@hotmail.com",
                    "benjamin.pottinger@gmail.com",
                    "capulet211@gmail.com",
                    "squidlyorders@gmail.com",
                    "dacoast@gmail.com",
                    "peeganstein@gmail.com",
                    "jkdenker@gmail.com",
                    "eubanks.monique@gmail.com",
                    "hitomi.mwong@gmail.com",
                    "taranw1980@aol.com",
                    "bello3232@aol.com",
                    "csavero@gmail.com",
                    "mrhndcuffs@hotmail.com",
                    "livininstyle03@icloud.com",
                    "a1_tink@yahoo.com",
                    "low4own@yahoo.com",
                    "Bringitback2u@aol.com",
                    "colemandk@yahoo.com",
                    "itsmikespears@gmail.com",
                    "xxcalibur@gmail.com",
                    "awsomeanimeaddicts@gmail.com",
                    "tabitha_raye@yahoo.com",
                    "jsprout3@yahoo.com",
                    "anfbro4u@yahoo.com",
                    "mochahontas@hotmail.com",
                    "pdmcv61@gmail.com",
                    "johneller@citlink.net",
                    "Shone314@yahoo.com",
                    "tlcmjbdfan412@gmail.com",
                    "ronisha.snell@yahoo.com",
                    "denuntadial@gmail.com",
                    "umikim80@gmail.com",
                    "liyahfan4life@yahoo.com",
                    "thutchcr@comcast.net",
                    "seth937@gmail.com",
                    "nek8006@yahoo.com",
                    "omfg.itsme93@gmail.com",
                    "joshua.mcmahan1@gmail.com",
                    "morganjyapa@yahoo.com",
                    "tiffanirodriguez@gmail.com",
                    "shani924@gmail.com",
                    "shaq3672@yahoo.com",
                    "vcanalita16@gmail.com",
                    "MELINDA.blanco@yahoo.com",
                    "itsmrstarzs@gmail.com",
                    "xzvredvzx@aol.com",
                    "kmfc2010@yahoo.com",
                    "Sthunderrocker@gmail.com",
                    "dwatkins418@gmail.com",
                    "to.stella@gmail.com",
                    "thanorthwest@gmail.com",
                    "jgta123@yahoo.com",
                    "anitra.utley0720@gmail.com",
                    "varickbrown@yahoo.com",
                    "nicnaughty69@aol.com",
                    "cameron.quintana@yahoo.com",
                    "ruben.beaver@gmail.com",
                    "mparker3579@yahoo.com",
                    "speedball32@hotmail.com",
                    "JusticeKBarber@gmail.com",
                    "JusticeBowers@gmail.com",
                    "doh@maine.rr.com",
                    "sophienimcats@yahoo.com",
                    "daynauno@gmail.com",
                    "ed0jr18@gmail.com",
                    "theariale@msn.com",
                    "sthef360@yahoo.com",
                    "rmtjr81@gmail.com",
                    "raymondlook@gmail.com",
                    "latoya.jlee@hotmail.com",
                    "prsloan@outlook.com",
                    "princesselle22@aol.com",
                    "stephyuchiha@yahoo.com",
                    "empressdesigns@gmail.com",
                    "erinkaymills@yahoo.com",
                    "MarcMartinez48@yahoo.com",
                    "pwashii@yahoo.com",
                    "lefteye82501a@aim.com",
                    "dancer0083@aol.com",
                    "bobbilynnnn@aol.com",
                    "shiasmo8@gmail.com",
                    "jralph_garcia@yahoo.com",
                    "pinktink4@hotmail.com",
                    "gflores26@gmail.com",
                    "ignited_blue_FF2003@yahoo.com",
                    "dnyce.dareal@gmail.com",
                    "nelrock76@aol.com",
                    "kreilly999@aol.com",
                    "dmarieljones@yahoo.com",
                    "jamesmcgeorge06@gmail.com",
                    "twadli3400@yahoo.com",
                    "missazdiva@gmail.com",
                    "mikeyg2s@aol.com",
                    "blueyez39@hotmail.com",
                    "acslayton@gmail.com",
                    "Snozberries@cox.net",
                    "ignaciojose83@gmail.com",
                    "rickfhs@hotmail.com",
                    "tatianacass@yahoo.com",
                    "greenmilefan1986@gmail.com",
                    "fields1224@gmail.com",
                    "matrix368834@yahoo.com",
                    "courtneydmcknight@gmail.com",
                    "nielly86@icloud.com",
                    "heathermckay@columbus.rr.com",
                    "too_cute1976@hotmail.com",
                    "mzsheeka@yahoo.com",
                    "larrybarrontv@gmail.com",
                    "c.givan@mail.com",
                    "mark302@hotmail.com",
                    "pt00325@gmail.com",
                    "cmasini@asu.edu",
                    "e_sims85@yahoo.com",
                    "miguelmartinez2007@hotmail.com",
                    "rforte@nyc.rr.com",
                    "MJT12185@GMAIL.COM",
                    "kevmc1685@gmail.com",
                    "tyree102377@gmail.com",
                    "MichaelWeaver66@hotmail.com",
                    "carpexnoctem13@gmail.com",
                    "tamika_sheree@yahoo.com",
                    "nau4679@gmail.com",
                    "jeriel789@yahoo.com",
                    "jilliea.edwards@gmail.com",
                    "edotoh@gmail.com",
                    "Daviahicks1988@gmail.com",
                    "teague182@gmail.com",
                    "Junior_mendoza@outlook.com",
                    "mji006@gmail.com",
                    "kcborjal@gmail.com",
                    "Rockangeldancer@hotmail.com",
                    "cmc_0905@yahoo.com",
                    "lvhjr@aol.com",
                    "Tinamariedbrown@yahoo.com",
                    "moefowler@gmail.com",
                    "brejaungray92@gmail.com",
                    "sandpie10@yahoo.com",
                    "jbender@utzsnacks.com",
                    "kstokes706@yahoo.com",
                    "jrandrews79@att.net",
                    "electric.groove.theory@gmail.com",
                    "SAM17419@aol.com",
                    "krazysexikool07@gmail.com",
                    "cody.boydston@yahoo.com",
                    "shadezrel10hiddenred@yahoo.com",
                    "savoygardner@gmail.com",
                    "AdrianPatrickF@gmail.com",
                    "cjroconnolly@gmail.com",
                    "wanker_number_09@yahoo.com",
                    "awax_0914@hotmail.com",
                    "christinalopez8702@msn.com",
                    "breebreebrandon@gmail.com",
                    "courtney.webster.2073@gmail.com",
                    "faythejo@gmail.com",
                    "michaelb628@gmail.com",
                    "just4uevent@gmail.com",
                    "lurapleickhardt@aol.com",
                    "snowbabies08@yahoo.com",
                    "calvindereuck@hotmail.com",
                    "branzz87@aim.com",
                    "ajlacayo80@gmail.com",
                    "ibanezgurl425@gmail.com",
                    "hullabaloo.foodoo@gmail.com",
                    "briana_salter@yahoo.com",
                    "Noveau16@aol.com",
                    "jscore@gmail.com",
                    "risa0221mj@za.cyberhome.ne.jp",
                    "aki.tomonaga@gmail.com",
                    "winterbauers@hotmail.com",
                    "mzsabrinawilson@gmail.com",
                    "ruizmarcella@yahoo.com",
                    "corimcgee2001@gmail.com",
                    "acappuso@yahoo.com",
                    "kristi.jackson@stanfordalumni.org",
                    "hellosunlightsetmeup@gmail.com",
                    "truckerxtyle@gmail.com",
                    "sayoko17@hotmail.com",
                    "lobamystica@hotmail.com"];

                $conditions = [
                    'tier_id' => [
                        'condition' => '=',
                        'value' => $tier_id
                    ],
                    'email' => [
                        'condition' => 'IN',
                        'value' => $emails
                    ]
                ];

                $this->db->setData(
                    'external_fulfillment_orders',
                    ['shipping_postal'=>""],
                    $conditions
                );
            }

            return $user_jobs;
        }
    }

    public function getTiersByJob($job_id)
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => $job_id
            ]
        ];

        if (!$tiers = $this->db->getData(
            'CommercePlant_getExternalFulfillmentTiersAndOrderCount', false, $conditions
        )
        ) {
            return false;
        } else {
            return $tiers;
        }
    }

    public function getOrderCountByJob($job_id = false, $filter = false)
    {

        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => ($job_id) ? $job_id : $this->fulfillment_job
            ]
        ];

        // filter by some such thing (really, just complete, but left it open)
        if (is_array($filter)) {
            $conditions = array_merge([
                $filter['name'] => [
                    'condition' => '=',
                    'value' => ($filter['value']) ? 1 : 0
                ]
            ], $conditions);
        }

        if (!$order_count = $this->db->getData(
            'CommercePlant_getOrderCountByJob', false, $conditions
        )
        ) {
            return false;
        } else {
            return $order_count[0]['total_orders'];
        }
    }

    public function getTiersByJobCount($job_id)
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => $job_id
            ]
        ];

        if (!$tiers = $this->db->getData(
            'external_fulfillment_tiers', 'count(*) as total_tiers', $conditions
        )
        ) {
            return false;
        } else {
            return $tiers[0]['total_tiers'];
        }
    }

    public function parseUpload($files)
    {

        $this->uploaded_files = $files;

        for ($i = 0; $i < count($this->uploaded_files['name']); $i++) {

            // get file contents
            $file_contents = CASHSystem::getFileContents($this->uploaded_files['tmp_name'][$i]);

            if ($csv_to_array = CASHSystem::outputCSVToArray($file_contents)) {
                $this->raw_data[$this->uploaded_files['name'][$i]] = $csv_to_array['array'];

                $this->mappable_fields = array_merge(
                    $this->mappable_fields,
                    $csv_to_array['unique_fields']
                );

                return $this;
            } else {

                // we should throw an exception here, actually
                return false;
            }

        }

        if ($this->checkMinimumMappableFields()) {
            // so we've ascertained this is a kickstarter import, so let's try to map these base fields
            $this->has_minimal_mappable_fields = true;
        }

        return $this;
    }

    public function checkMinimumMappableFields()
    {
        // we need to check if these CSVs have the structure we're expecting
        //TODO: this needs to be more dynamic
        //TODO: I added shitty shit for this that needs to be taken out
        if (
            in_array("Backer Name", $this->mappable_fields) ||
            in_array("Shipping Name", $this->mappable_fields) ||
            in_array("First Name", $this->mappable_fields)
        ) {
            $this->minimum_field_requirements['name'] = true;
        }

        if (
        in_array("Email", $this->mappable_fields) ||
        in_array("E Mail", $this->mappable_fields)
        ) {
            $this->minimum_field_requirements['email'] = true;
        }

        // if we didn't find any of the fields we're looking for, we need to do this manually
        if (in_array(false, $this->minimum_field_requirements)) {
            return false;
        }

        return true;
    }

    /*    public function standardizeOrderArray() {
            //var_dump($this->mappable_fields);
            // it's an array of CSV files; each CSV file has rows. so here we're looping through files, not rows of data
            foreach($this->raw_data as $csv_set) {
                // then here we're looping through rows in each file. each row is an order.
                foreach ($csv_set as $csv_row) {
                    //var_dump($csv_row); // this is showing the right fields
                    $row = [];
                    // we have to map each field in each raw_data row to one big standardized array

                    foreach ($this->mappable_fields as $mappable_key) {
                        // mappable field key exists in this row of raw data, so let's map it
                        $mappable_key = trim($mappable_key);
                        $row[$mappable_key] = "";

                        if (array_key_exists($mappable_key, $csv_row)) {
                            $row[$mappable_key] = $csv_row[$mappable_key];
                        }
                    }

                    //var_dump($row); // this is also showing the correct amount of fields
                    $this->parsed_data[] = $row;
                }
                //var_dump($this->parsed_data); // this shows all rows, but it's missing a bunch of fields on a lot of the rows
            }

            if ($this->checkMinimumMappableFields()) {
                // this is a kickstarter import.
                $this->mapStandardFields();

            } else {
                // we need to map fields manually.
            }

            return $this;
        }*/

    public function createFulfillmentJob($asset_id, $name, $description = "")
    {
        if (!$fulfillment_job = $this->db->setData(
            'external_fulfillment_jobs',
            array(
                'user_id' => $this->user_id,
                'asset_id' => $asset_id,
                'name' => $name,
                'description' => $description,
                'mappable_fields' => json_encode($this->mappable_fields),
                'has_minimum_mappable_fields' => $this->has_minimal_mappable_fields
            )
        )
        ) {
            return false;
        } else {

            $this->fulfillment_job = $fulfillment_job;
            return true;
        }
    }

    public function updateFulfillmentJob($values, $id = false)
    {

        // allows us to manually override
        if (!$id) {
            $id = $this->fulfillment_job;
        } else {
            // trickle down to the next method
            $this->fulfillment_job = $id;
        }

        if (!empty($values)) {

            $conditions = [
                'user_id' => [
                    'condition' => '=',
                    'value' => $this->user_id
                ],
                'id' => [
                    'condition' => '=',
                    'value' => $id
                ]
            ];

            $this->db->setData(
                'external_fulfillment_jobs',
                $values,
                $conditions
            );
        }

        return $this;
    }

    public function getFulfillmentJobByUserId($status)
    {

        if (!$status) {
            $status = 'created';
        }

        if (is_array($status)) {

            $conditions = [
                'user_id' => [
                    'condition' => '=',
                    'value' => $this->user_id
                ],
                'status' => [
                    'condition' => 'IN',
                    'value' => $status
                ]
            ];
        } else {

            $conditions = [
                'user_id' => [
                    'condition' => '=',
                    'value' => $this->user_id
                ],
                'status' => [
                    'condition' => '=',
                    'value' => $status
                ]
            ];
        }

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $conditions, 1, 'id DESC'
        )
        ) {

            return false;
        } else {
            // map some fields from the results
            $this->asset_id = $fulfillment_job[0]['asset_id'];
            $this->job_name = $fulfillment_job[0]['name'];
            $this->mappable_fields = json_decode($fulfillment_job[0]['mappable_fields']);
            $this->has_minimum_mappable_fields = (bool)$fulfillment_job[0]['has_minimum_mappable_fields'];
            $this->fulfillment_job = $fulfillment_job[0]['id'];
            $this->status = $fulfillment_job[0]['status'];

            return true;
        }
    }

    public function createFulfillmentTier($process_id, $name, $upc, $data)
    {

        if (!$fulfillment_tier = $this->db->setData(
            'external_fulfillment_tiers',
            array(
                'system_job_id' => $this->system_job_id,
                'fulfillment_job_id' => $this->fulfillment_job,
                'process_id' => $process_id,
                'user_id' => $this->user_id,
                'name' => $name,
                'upc' => $upc,
                'metadata' => json_encode($data)
            )
        )
        ) {
            return false;
        }

        return $fulfillment_tier;
    }

    public function processOrder($order, $tier_id)
    {
        $order_mapped = [];

        foreach ($this->mapped_fields as $destination_field => $source_field) {
            // we can deal with the minimum expected fields first, and go from there
            if (!empty($order[$source_field])) {
                $source = empty($order[$source_field]) ? '' : $order[$source_field];
            }

            // fallback if it's empty or not set
            if (empty($order[$source_field])) {

                //TODO: put this back in
                // the ol' digital order switcheroo
                if ($source_field == "Shipping Name") {
                    $source = empty($order["Backer Name"]) ? '' : $order["Backer Name"];
                } else {
                    $source = "";
                }
            }

            // either way this is now mapped correctly
            $order_mapped[$destination_field] = $source;
            CASHSystem::errorLog($destination_field . " => " . $source);
        }

        // hack the system
        $order_mapped['notes'] = empty($order["Notes"]) ? '' : $order["Notes"];
        $order_mapped['order_data'] = json_encode($order);

        $order_mapped['tier_id'] = $tier_id;

        // create order
        $this->createOrder($order_mapped);
    }

    public function createOrder($order_details)
    {

        if (!$order_id = $this->db->setData(
            'external_fulfillment_orders', $order_details
        )
        ) {
            return false;
        }

        $this->generateDownloadCode($order_id);

        return $this;
    }

    public function createOrContinueJob($status = false)
    {

        if ($this->getFulfillmentJobByUserId($status)) {

            $this->createOrGetSystemJob();

            if (!empty($_REQUEST['fulfillment_name'])) {
                // just in case this is one of those stray
                $job_name = $_REQUEST['fulfillment_name'] ? $_REQUEST['fulfillment_name'] : "";
                $description = $_REQUEST['fulfillment_description'] ? $_REQUEST['fulfillment_description'] : "";

                $this->updateFulfillmentJob([
                    'name' => $job_name,
                    'description' => $description
                ]);

                $this->job_name = $job_name;
            }

            return $this;
        } else {
            // create external fulfillment job (asset id, job name FPO)
            $job_name = $_REQUEST['fulfillment_name'] ? $_REQUEST['fulfillment_name'] : "";
            $description = $_REQUEST['fulfillment_description'] ? $_REQUEST['fulfillment_description'] : "";

            $this->createFulfillmentJob(0, $job_name, $description);
            $this->createOrGetSystemJob();

            $this->status = "created";
        }

        $this->job_name = $job_name;

        return $this;

    }

    public function createOrGetSystemJob()
    {
        // get or create cash queue job object
        if ($this->queue = new CASHQueue(
            $this->user_id,
            $this->fulfillment_job,
            'external_fulfillment_jobs')
        ) {

            //

        } else {
            // return an error
        }
    }

    public function createJobProcesses()
    {
        // insert raw data into system processes, per CSV; then use process id to insert into fulfillment jobs
        foreach ($this->raw_data as $filename => $tier) {

            $this->queue->createSystemProcess(
                $tier,                          // raw data, for parity
                basename($filename, '.csv')     // this could be anything, but naming the process by filename seems okay
            );
        }

        return $this;
    }

    public function getJobProcesses()
    {
        if (!$processes = $this->queue->getSystemProcessesByJob()) {
            return false;
        }

        return $processes;
    }

    public function updateFulfillmentJobStatus($status)
    {

        $condition = array(
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'id' => [
                'condition' => '=',
                'value' => $this->fulfillment_job
            ],
            'status' => [
                'condition' => '=',
                'value' => $this->status
            ]
        );

        if (!$fulfillment_tier = $this->db->setData(
            'external_fulfillment_jobs',
            array(
                'status' => $status
            ),
            $condition

        )
        ) {
            return false;
        }

        $this->status = $status;

        return $this;
    }

    public function createTiers()
    {

        // hit system jobs with table_id, type to get master job id
        if (!$this->queue) {

            // there's no valid queue object, which means something went wrong when we tried to load or create
            return false;
        } else {
            $this->system_job_id = $this->queue->job_id;

            if (!$this->has_minimum_mappable_fields) {
                // we need to have them map shit
            }

            // get processes by the system job id, and loop through them if there are any
            if ($system_processes = $this->queue->getSystemProcessesByJob()) {
                foreach ($system_processes as $process) {
                    // loop through system processes
                    if ($data = json_decode($process['data'], true)) {

                        // create tiers
                        if ($tier_id = $this->createFulfillmentTier($process['id'], $process['name'], '', $data)) {
                            //orders per tier

                            foreach ($data as $order) {
                                // loop through each order and store it in the database
                                $this->processOrder($order, $tier_id);
                            }
                        }

                        // if no errors, delete this system process
                        $this->queue->deleteSystemProcess($process['id'], $this->system_job_id);

                    }


                }
            }

            // delete the system job
            $this->queue->deleteSystemJob();

        }

        return $this;

    }

    /**
     * Update tiers on existing job, from the details page
     *
     * @return $this|bool
     */
    public function updateTiers()
    {

        if (!empty($_REQUEST['tier_name']) && count($_REQUEST['tier_name']) > 0) {

            foreach ($_REQUEST['tier_name'] as $tier_id => $tier_name) {
                // update tier
                $tier_name = isset($_REQUEST['tier_name'][$tier_id])
                    ? $_REQUEST['tier_name'][$tier_id] : $tier_name;

                $upc = isset($_REQUEST['tier_upc'][$tier_id])
                    ? $_REQUEST['tier_upc'][$tier_id] : "";

                $physical = isset($_REQUEST['tier_physical'][$tier_id])
                    ? $_REQUEST['tier_physical'][$tier_id] : 0;

                $shipped = isset($_REQUEST['tier_shipped'][$tier_id])
                    ? time() : 0;

                //TODO: mark all orders under this tier as shipped

                $conditions = [
                    'user_id' => [
                        'condition' => '=',
                        'value' => $this->user_id
                    ],
                    'id' => [
                        'condition' => '=',
                        'value' => $this->fulfillment_job
                    ],
                    'id' => [
                        'condition' => '=',
                        'value' => $tier_id
                    ]
                ];

                $this->db->setData(
                    'external_fulfillment_tiers',
                    [
                        'name' => $tier_name,
                        'upc' => $upc,
                        'physical' => $physical,
                        'shipped' => $shipped
                    ],
                    $conditions

                );

                // we also want to mark all orders inside this tier as completed,
                // with the timestamp for reporting (assuming it's shipped)
                if (!empty($shipped)) {
                    $conditions = [
                        'complete' => [
                            'condition' => '=',
                            'value' => 0
                        ],
                        'tier_id' => [
                            'condition' => '=',
                            'value' => $tier_id
                        ]
                    ];

                    $this->db->setData(
                        'external_fulfillment_orders',
                        [
                            'complete' => time()
                        ],
                        $conditions

                    );
                }

            }

        }

        return $this;

    }

    public function deleteJob($job_id)
    {

        // get tiers for this job
        if ($tiers = $this->getTiersByJob($job_id)) {
            // loop through tiers and delete orders
            foreach ($tiers as $tier) {

                // delete orders
                $this->db->deleteData(
                    'external_fulfillment_orders', [
                        'tier_id' => [
                            'condition' => '=',
                            'value' => $tier['id']
                        ]
                    ]
                );
            }
        }

        // delete tiers
        $this->db->deleteData(
            'external_fulfillment_tiers', [
                'fulfillment_job_id' => [
                    'condition' => '=',
                    'value' => $job_id
                ]
            ]
        );

        // delete job
        $this->db->deleteData(
            'external_fulfillment_jobs', [
                'id' => [
                    'condition' => '=',
                    'value' => $job_id
                ]
            ]
        );
    }

    /**
     * Get all orders since timestamp
     *
     * @param $timestamp
     */
    public static function getOrders($start_date=0, $end_date=0, $physical=true)
    {
        $conditions = [
            'start_date' => [
                'condition' => '=',
                'value' => $start_date
            ],
            'end_date' => [
                'condition' => '=',
                'value' => $end_date
            ],
            'physical' => [
                'condition' => '=',
                'value' => ($physical) ? 1 : 0
            ]
        ];

        $data_connection = new CASHRequest(null);

        if (!$data_connection->db) $data_connection->connectDB();

        // we're only getting stuff newer than $timestamp, and also where tier upc IS NOT NULL
        $orders = $data_connection->db->getData(
            'CommercePlant_getExternalFulfillmentOrdersByTimestamp', false, $conditions
        );

        return $orders;
    }

    /**
     * @param $order_id
     */
    public function generateDownloadCode($order_id)
    {
        if (!$add_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'addlockcode',
                'scope_table_alias' => 'external_fulfillment_orders',
                'scope_table_id' => $order_id
            )
        )
        ) {
            return false;
        }

        return true;
    }
    
    public function getBackersForJob($fulfillment_job_id) {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => $fulfillment_job_id
            ]
        ];

        CASHSystem::errorLog($conditions);

        if (!$backers = $this->db->getData(
            'CommercePlant_getExternalFulfillmentBackersByJob', false, $conditions
        )
        ) {
            return false;
        } else {
            return $backers;
        }
    }

}