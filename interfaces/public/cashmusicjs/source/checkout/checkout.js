(function() {
    'use strict';
    var cm = window.cashmusic;

    /***************************************************************************************
     *
     * window.cashmusic.stripe (object)
     * Handle Stripe.com payment token generation
     *
     * PUBLIC-ISH FUNCTIONS
     * window.cashmusic.stripe.generateToken(string key, target source)
     *
     ***************************************************************************************/
    cm.stripe = {
        eventAttached: false,

        getYears: function() {
            var year =  new Date().getFullYear();
            var years = {};
            for (var i = 0; i < 20; i++) {
                years[year] = year;
                year++;
            }
            return years;
        },

        generateToken: function(key,source) {
            var cm = window.cashmusic;
            if (cm.embedded) {
                cm.events.fire(cm,'stripetokenrequested',params);
            } else {
                cm.loadScript('https://js.stripe.com/v2/', function() {
                    var formElements = [];
                    formElements.push({id: "name", type: "text", placeholder: "Cardholder name"});
                    formElements.push({id: "email", type: "email", placeholder: "Email address"});
                    formElements.push({id: "card-number", type: "text", placeholder: "Credit card number"});
                    formElements.push({id: "card-expiry-month", type: "select", options: {
                        "01":"01: Jan",
                        "02":"02: Feb",
                        "03":"03: Mar",
                        "04":"04: Apr",
                        "05":"05: May",
                        "06":"06: Jun",
                        "07":"07: Jul",
                        "08":"08: Aug",
                        "09":"09: Sep",
                        "10":"10: Oct",
                        "11":"11: Nov",
                        "12":"12: Dec"
                    }, value:"01"});
                    formElements.push({id: "card-expiry-year", type: "select", options: cm.stripe.getYears(), placeholder: new Date().getFullYear()});
                    formElements.push({id: "card-cvc", type: "text", placeholder: "CVV"});
                    formElements.push({id: "stripe-submit", type: "submit", text: "Submit Payment"});
                    cm.userinput.getInput(formElements,'getstripetoken', null, ""); //TODO: total price for checkout
                    if (!cm.stripe.eventAttached) {
                        cm.events.add(cm,'userinput', function(e) {
                            if (e.detail['cm-userinput-type'] == 'getstripetoken') {
                                Stripe.setPublishableKey(key);
                                Stripe.card.createToken({
                                    name: e.detail['name'],
                                    number: e.detail['card-number'],
                                    cvc: e.detail['card-cvc'],
                                    exp_month: e.detail['card-expiry-month'],
                                    exp_year: e.detail['card-expiry-year']
                                }, function(status, response, evt) {
                                    if (response.error) {
                                        // Show the errors on the form
                                        document.getElementById('cm-userinput-message').innerHTML = response.error.message;
                                        cm.styles.addClass(document.getElementsByClassName('cm-userinput-container')[0],'nope');
                                        setTimeout(function(){
                                            cm.styles.removeClass(document.getElementsByClassName('cm-userinput-container')[0],'nope');
                                        }, 800);
                                    } else {
                                        // response contains id and card, which contains additional card details
                                        cm.storage['checkoutdata']['stripe'] = response.id;
                                        cm.storage['checkoutdata']['name']   = e.detail['name'];
                                        cm.storage['checkoutdata']['email']  = e.detail['email'];
                                        cm.events.fire(cm,'checkoutdata',cm.storage['checkoutdata'],source);
                                        cm.overlay.reveal('<div class="cm-loading"></div>');
                                    }
                                });

                            }
                        });
                        cm.stripe.eventAttached = true;
                    }
                });
            }
        }
    };

    /***************************************************************************************
     *
     * window.cashmusic.userinput (object)
     * pass in an array of needed information, it builds forms, you get answers back
     *
     * EXAMPLES:
     * event driven, so first pass in:
     *		[
     *			{id: "name", type: "text", placeholder: "an input", required: true},
     *			{id: "name", type: "hidden", value: "secret"},
     *			{id: "submit", type: "submit", text: "submit"}
     *		]
     *
     * then listen for an answer:
     * 	cm.events.add(cm,'checkoutdata', function(e) {
	 *	 		console.log(e.detail);
	 * 	});
     *
     * PUBLIC-ISH FUNCTIONS
     * window.cashmusic.userinput.getinput(array elements, string type)
     *
     ***************************************************************************************/
    cm.userinput = {
        getInput: function (elements,type,style,msg) {
            type = type || 'unknown';
            var form = document.createElement('form');
            var container = document.createElement('div');
            container.className = 'cm-userinput-container';
            var message = document.createElement('div');
            message.id = 'cm-userinput-message';
            message.innerHTML = '&nbsp;';

            if (msg) {
                message.innerHTML = msg;
            }
            form.className = 'cm-userinput ' + type + ' ' + style;

            elements.push({id:'cm-userinput-type', type:'hidden', value:type});

            for (var i = 0; i < elements.length; i++) {
                var element = elements[i];
                if (element.type !== "submit" && element.type !== "select") {
                    var input = document.createElement("input");
                    input.type = element.type;
                    input.placeholder = element.placeholder;
                    if (element.value) {
                        input.value = element.value;
                    }
                } else {
                    if (element.type == "select") {
                        var input = document.createElement("select");
                        var codes = Object.keys(element.options);
                        codes.sort(function(a, b) {
                            return a - b;
                        });
                        for (var n = 0; n < codes.length; n++) {
                            var option = document.createElement("option");
                            option.value = codes[n];
                            option.text = element.options[codes[n]];
                            if (element.value == codes[n]) {
                                option.selected = 'selected';
                            }
                            input.appendChild(option);
                        }
                    } else {
                        var input = document.createElement("button");
                        input.type = "submit";
                        input.innerHTML = element.text;
                    }
                }
                input.name = element.id;
                input.id = "cm-userinput-" + type + "-" + element.id;
                if (element.required) {
                    input.setAttribute('data-required','1');
                }

                if (element.id == "stripe-submit" && cm.storage.checkoutdata.total) {
                    // show the final amount to be charged

                    var total = document.createElement('div');
                    total.id = 'cm-amount-message';

                    if (!cm.storage.checkoutdata.transaction_message) {
                        cm.storage.checkoutdata.transaction_message = "";
                    }


                    total.innerHTML = '<h2 class="cm-pricing">Transaction amount: <span>'+cm.storage.checkoutdata.total+cm.storage.checkoutdata.transaction_message+"</span></h2><!--cm-pricing-->";

                    form.appendChild(total);
                }

                form.appendChild(input);
            }

            container.appendChild(message);
            container.appendChild(form);

            cm.events.add(form,'submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var formdata = {};
                var incomplete = false;
                for ( var i = 0; i < form.elements.length; i++ ) {
                    var e = form.elements[i];
                    formdata[e.name] = e.value;
                    if (e.getAttribute('data-required') && e.value == '') {
                        incomplete = true;
                        cm.styles.addClass(e,'incomplete');
                    } else {
                        cm.styles.removeClass(e,'incomplete');
                    }
                }
                if (incomplete) {
                    // Show the errors on the form
                    document.getElementById('cm-userinput-message').innerHTML = 'Please complete all required fields.';
                    cm.styles.addClass(document.getElementsByClassName('cm-userinput-container')[0],'nope');
                    setTimeout(function(){
                        cm.styles.removeClass(document.getElementsByClassName('cm-userinput-container')[0],'nope');
                    }, 800);
                } else {
                    cm.events.fire(cm,'userinput',formdata);
                }
            });

            cm.overlay.reveal(container);
        }
    };

    /***************************************************************************************
     *
     * window.cashmusic.checkout (object)
     * start the checkout flow for multiple payments in a controlled overlay
     *
     * PUBLIC FUNCTIONS
     * window.cashmusic.checkout.begin(obj options, target source)
     *
     * EXAMPLE
     cm.checkout.begin({
		 		"stripe"   : "pk_test_wh4t3ver", // false OR public stripe key
		 		"paypal"   : true, // boolean for "should we have paypal as an option"
		 		"currency" : "USD", // USD = default, but this also determines what country is
								 auto-selected in the drop-down
		 		"shipping" : {"r1":"US & Canada ($4)","r2":"Rest of world ($7)"}, // or bool.
								 false = no shipping, true = shipping address but no region selector
		 		"testing"  : true // allows stripe to work without SSL if true, false or omit
								 (preferable for production) to enforce
	 		});
     *
     ***************************************************************************************/
    cm.checkout = {
        prepped: false,

        countries: {
            "AF":"Afghanistan",
            "AX":"Åland Islands",
            "AL":"Albania",
            "DZ":"Algeria",
            "AS":"American Samoa",
            "AD":"Andorra",
            "AO":"Angola",
            "AI":"Anguilla",
            "AQ":"Antarctica",
            "AG":"Antigua and Barbuda",
            "AR":"Argentina",
            "AM":"Armenia",
            "AW":"Aruba",
            "AU":"Australia",
            "AT":"Austria",
            "AZ":"Azerbaijan",
            "BS":"Bahamas",
            "BH":"Bahrain",
            "BD":"Bangladesh",
            "BB":"Barbados",
            "BY":"Belarus",
            "BE":"Belgium",
            "BZ":"Belize",
            "BJ":"Benin",
            "BM":"Bermuda",
            "BT":"Bhutan",
            "BO":"Bolivia",
            "BQ":"Bonaire, Saint Eustatius and Saba",
            "BA":"Bosnia-Herzegovina",
            "BW":"Botswana",
            "BV":"Bouvet Island",
            "BR":"Brazil",
            "IO":"British Indian Ocean Territory",
            "BN":"Brunei Darussalam",
            "BG":"Bulgaria",
            "BF":"Burkina Faso",
            "BI":"Burundi",
            "KH":"Cambodia",
            "CM":"Cameroon",
            "CA":"Canada",
            "CV":"Cape Verde",
            "KY":"Cayman Islands",
            "CF":"Central African Republic",
            "TD":"Chad",
            "CL":"Chile",
            "CN":"China",
            "CX":"Christmas Island",
            "CC":"Cocos (Keeling) Islands",
            "CO":"Colombia",
            "KM":"Comoros",
            "CG":"Congo",
            "CD":"Congo, the Democratic Republic of the",
            "CK":"Cook Islands",
            "CR":"Costa Rica",
            "CI":"Côte d’Ivoire",
            "HR":"Croatia",
            "CU":"Cuba",
            "CW":"Curacao",
            "CY":"Cyprus",
            "CZ":"Czech Republic",
            "DK":"Denmark",
            "DJ":"Djibouti",
            "DM":"Dominica",
            "DO":"Dominican Republic",
            "EC":"Ecuador",
            "EG":"Egypt",
            "SV":"El Salvador",
            "GQ":"Equatorial Guinea",
            "ER":"Eritrea",
            "EE":"Estonia",
            "ET":"Ethiopia",
            "FK":"Falkland Islands",
            "FO":"Faroe Islands",
            "FJ":"Fiji",
            "FI":"Finland",
            "FR":"France",
            "GF":"French Guiana",
            "PF":"French Polynesia",
            "TF":"French Southern Territories",
            "GA":"Gabon",
            "GM":"Gambia",
            "GE":"Georgia",
            "DE":"Germany",
            "GH":"Ghana",
            "GI":"Gibraltar",
            "GR":"Greece",
            "GL":"Greenland",
            "GD":"Grenada",
            "GP":"Guadeloupe",
            "GU":"Guam",
            "GT":"Guatemala",
            "GG":"Guernsey",
            "GN":"Guinea",
            "GW":"Guinea-Bissau",
            "GY":"Guyana",
            "HT":"Haiti",
            "HM":"Heard Island and McDonald Islands",
            "VA":"Holy See (Vatican City State)",
            "HN":"Honduras",
            "HK":"Hong Kong",
            "HU":"Hungary",
            "IS":"Iceland",
            "IN":"India",
            "ID":"Indonesia",
            "IR":"Iran, Islamic Republic of",
            "IQ":"Iraq",
            "IE":"Ireland",
            "IM":"Isle of Man",
            "IL":"Israel",
            "IT":"Italy",
            "JM":"Jamaica",
            "JP":"Japan",
            "JE":"Jersey",
            "JO":"Jordan",
            "KZ":"Kazakhstan",
            "KE":"Kenya",
            "KI":"Kiribati",
            "KP":"North Korea",
            "KR":"Korea",
            "KW":"Kuwait",
            "KG":"Kyrgyzstan",
            "LA":"Lao People’s Democratic Republic",
            "LV":"Latvia",
            "LB":"Lebanon",
            "LS":"Lesotho",
            "LR":"Liberia",
            "LY":"Libyan Arab Jamahiriya",
            "LI":"Liechtenstein",
            "LT":"Lithuania",
            "LU":"Luxembourg",
            "MO":"Macao",
            "MK":"Macedonia, the former Yugoslav Republic of",
            "MG":"Madagascar",
            "MW":"Malawi",
            "MY":"Malaysia",
            "MV":"Maldives",
            "ML":"Mali",
            "MT":"Malta",
            "MH":"Marshall Islands",
            "MQ":"Martinique",
            "MR":"Mauritania",
            "MU":"Mauritius",
            "YT":"Mayotte",
            "MX":"Mexico",
            "FM":"Micronesia, Federated States of",
            "MD":"Moldova, Republic of",
            "MC":"Monaco",
            "MN":"Mongolia",
            "ME":"Montenegro",
            "MS":"Montserrat",
            "MA":"Morocco",
            "MZ":"Mozambique",
            "MM":"Myanmar",
            "NA":"Namibia",
            "NR":"Nauru",
            "NP":"Nepal",
            "NL":"Netherlands",
            "NC":"New Caledonia",
            "NZ":"New Zealand",
            "NI":"Nicaragua",
            "NE":"Niger",
            "NG":"Nigeria",
            "NU":"Niue",
            "NF":"Norfolk Island",
            "MP":"Northern Mariana Islands",
            "NO":"Norway",
            "OM":"Oman",
            "PK":"Pakistan",
            "PW":"Palau",
            "PS":"Palestinian Territory, Occupied",
            "PA":"Panama",
            "PG":"Papua New Guinea",
            "PY":"Paraguay",
            "PE":"Peru",
            "PH":"Philippines",
            "PN":"Pitcairn",
            "PL":"Poland",
            "PT":"Portugal",
            "PR":"Puerto Rico",
            "QA":"Qatar",
            "RE":"Réunion",
            "RO":"Romania",
            "RU":"Russian Federation",
            "RW":"Rwanda",
            "BL":"Saint Barthélemy",
            "SH":"Saint Helena, Ascension and Tristan da Cunha",
            "KN":"Saint Kitts and Nevis",
            "LC":"Saint Lucia",
            "MF":"Saint Martin (French part)",
            "PM":"Saint Pierre and Miquelon",
            "VC":"Saint Vincent and the Grenadines",
            "WS":"Samoa",
            "SM":"San Marino",
            "ST":"Sao Tome and Principe",
            "SA":"Saudi Arabia",
            "SN":"Senegal",
            "RS":"Serbia",
            "SC":"Seychelles",
            "SL":"Sierra Leone",
            "SG":"Singapore",
            "SX":"Sint Maarten (Dutch part)",
            "SK":"Slovakia",
            "SI":"Slovenia",
            "SB":"Solomon Islands",
            "SO":"Somalia",
            "ZA":"South Africa",
            "GS":"South Georgia and the South Sandwich Islands",
            "ES":"Spain",
            "LK":"Sri Lanka",
            "SD":"Sudan",
            "SR":"Suriname",
            "SJ":"Svalbard and Jan Mayen",
            "SZ":"Swaziland",
            "SE":"Sweden",
            "CH":"Switzerland",
            "SY":"Syrian Arab Republic",
            "TW":"Taiwan, Province of China",
            "TJ":"Tajikistan",
            "TZ":"Tanzania, United Republic of",
            "TH":"Thailand",
            "TL":"Timor-Leste",
            "TG":"Togo",
            "TK":"Tokelau",
            "TO":"Tonga",
            "TT":"Trinidad and Tobago",
            "TN":"Tunisia",
            "TR":"Turkey",
            "TM":"Turkmenistan",
            "TC":"Turks and Caicos Islands",
            "TV":"Tuvalu",
            "UG":"Uganda",
            "UA":"Ukraine",
            "AE":"United Arab Emirates",
            "GB":"United Kingdom",
            "US":"United States",
            "UY":"Uruguay",
            "UZ":"Uzbekistan",
            "VU":"Vanuatu",
            "VE":"Venezuela, Bolivarian Republic of",
            "VN":"Viet Nam",
            "VG":"Virgin Islands, British",
            "VI":"Virgin Islands, U.S.",
            "WF":"Wallis and Futuna",
            "EH":"Western Sahara",
            "YE":"Yemen",
            "ZM":"Zambia",
            "ZW":"Zimbabwe"
        },

        prep: function () {
            if (!cm.checkout.prepped) {
                // add in styles
                cm.styles.injectCSS(cm.path + '/templates/checkout.css',false,true);
                cm.checkout.prepped = true;
            }
        },

        begin: function (options,source) {
            if (cm.embedded) {
                cm.events.fire(cm,'begincheckout',options);
            } else {
                cm.checkout.prep();
                // set up the empty object we'll populate in the return
                cm.storage['checkoutdata'] = {
                    'stripe'   :false,
                    'paypal'   :false,
                    'shipping' :false,
                    'currency' :false,
                    'name'     :false,
                    'email'    :false,
                    'recurring':false,
                    'origin'   :window.location.href,
                    'total'	   :false,
                    'transaction_message':false
                };
                // detect SSL for stripe
                if (location.protocol !== 'https:' && options.testing !== true) {
                    options.stripe = false;
                }

                // recurring payments
                if (options.recurring) {
                    cm.storage['checkoutdata'].recurring = true;
                }

                if (options.transaction_message) {
                    cm.storage['checkoutdata'].transaction_message = options.transaction_message;
                }

                if (options.total) {
                    cm.storage['checkoutdata'].total = options.total;
                }

                // choose defaults by currency
                if (options.shipping) {
                    // this push business feels really dumb but we need a proper length count
                    // and using a single array literal wasn't expanding properly via push
                    var shippingElements = [];
                    shippingElements.push(
                        {id: "name", type: "text", placeholder: "Ship to name", required: true},
                        {id: "address1", type: "text", placeholder: "Shipping address 1", required: true},
                        {id: "address2", type: "text", placeholder: "Shipping address 2"},
                        {id: "city", type: "text", placeholder: "City", required: true},
                        {id: "state", type: "text", placeholder: "State/Province/Region", required: true},
                        {id: "postalcode", type: "text", placeholder: "Postal code", required: true}
                    );
                    if (options.stripe || options.paypal) {
                        var selectedCountry = "US";
                        if (options.currency) {
                            switch (options.currency) {
                                case "GBP":
                                    selectedCountry = "GB";
                                    break;
                                case "AUD":
                                    selectedCountry = "AU";
                                    break;
                                case "JPY":
                                    selectedCountry = "JP";
                                    break;
                                case "CAD":
                                    selectedCountry = "CA";
                                    break;
                                case "NZD":
                                    selectedCountry = "NZ";
                                    break;
                                case "HKD":
                                    selectedCountry = "HK";
                                    break;
                                case "MXN":
                                    selectedCountry = "MX";
                                    break;
                                case "NOK":
                                    selectedCountry = "NO";
                                    break;
                            }
                        }
                        // add in the country selector
                        shippingElements.push({id: "country", type: "select", options: cm.checkout.countries, value: selectedCountry});
                        // and if needed, the shipping region selector
                        if (!options.recurring) {
                            if (typeof options.shipping === 'object') {
                                shippingElements.push({id: "shipping-region", type: "select", options: {
                                    "":"Select shipping region",
                                    "r1":options.shipping.r1,
                                    "r2":options.shipping.r2
                                }, required: true});
                            }
                        }

                        // hey look a button!
                        shippingElements.push({id: "shipping-submit", type: "submit", text: "Set shipping info"});
                        // get the answers
                        if (typeof options.shipping === 'object') {
                            cm.userinput.getInput(shippingElements,'getshippingaddress','shipping');
                        } else {
                            cm.userinput.getInput(shippingElements,'getshippingaddress','noshipping');
                        }
                        // wait for them
                        cm.events.add(cm,'userinput', function(e) {
                            if (e.detail['cm-userinput-type'] == 'getshippingaddress') {
                                cm.storage['checkoutdata']['shipping'] = e.detail;
                                cm.checkout.initiatepayment(options,source);
                            }
                        });
                    } else {
                        cm.checkout.showerror();
                    }
                } else {
                    cm.checkout.initiatepayment(options,source);
                }
            }
        },

        initiatepayment: function (options,source) {
            // just starts the payment flow and does the steps needed based on
            // the options passed in...
            // Stripe only
            if (options.stripe && !options.paypal) {
                cm.stripe.generateToken(options.stripe,source);
            }
            // Paypal only
            else if (!options.stripe && options.paypal) {
                cm.storage['checkoutdata']['paypal'] = true;
                cm.events.fire(cm,'checkoutdata',cm.storage['checkoutdata'],source);
                cm.overlay.reveal('<div class="cm-loading"></div>');
            }
            // Stripe and Paypal
            else if (options.stripe && options.paypal) {
                // Create HTML elements to use as selectors
                var container = document.createElement("div");
                container.className = "cm-checkout-choose";

                var ppspan = document.createElement("span");
                var stspan = document.createElement("span");

                ppspan.innerHTML = 'Pay with PayPal';
                ppspan.className = 'pay-pp';
                stspan.innerHTML = 'Pay with a credit card';
                stspan.className = 'pay-cc';

                // Create a special event to detect Paypal chosen
                cm.events.add(ppspan,'click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    cm.storage['checkoutdata']['paypal'] = true;
                    cm.events.fire(cm,'checkoutdata',cm.storage['checkoutdata'],source);
                    cm.overlay.reveal('<div class="cm-loading"></div>');
                });

                // Create a special event to detect Stripe chosen
                cm.events.add(stspan,'click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    cm.stripe.generateToken(options.stripe,source);
                });

                container.appendChild(ppspan);
                container.appendChild(stspan);

                cm.overlay.reveal(container);

            }
            // No available payment types, by options or SSL limits on Stripe
            else {
                cm.checkout.showerror();
            }
        },

        showerror: function (type) {
            cm.overlay.reveal('<div class="cm-checkout-error">There are no valid payment types. Please add a payment connection. Check to make sure your site supports SSL (https) if you are using Stripe.</div>');
        }
    }

}()); // END
