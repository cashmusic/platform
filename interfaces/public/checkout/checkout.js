
(function() {
	'use strict';
	var cm = window.cashmusic;

	/***************************************************************************************
	 *
	 * window.cashmusic.stripe (object)
	 * Handle Stripe.com payment token generation
	 *
	 ***************************************************************************************/
	cm.stripe = {
		formElements: [
         {id: "name", type: "text", placeholder: "Cardholder name"},
         {id: "email", type: "email", placeholder: "Email address"},
         {id: "card-number", type: "text", placeholder: "Credit card number"},
         {id: "card-cvc", type: "text", placeholder: "CVV"},
         {id: "card-expiry-month", type: "text", placeholder: "12"},
         {id: "card-expiry-year", type: "text", placeholder: new Date().getFullYear()},
         {id: "stripe-submit", type: "submit", text: "Submit Payment"}
   	],

		generateToken: function(key,source) {
			var cm = window.cashmusic;
			if (cm.embedded) {
				cm.events.fire(cm,'stripetokenrequested',params);
			} else {
				cm.loadScript('https://js.stripe.com/v2/', function() {
					cm.userinput.getInput(cm.stripe.formElements,'getstripetoken');
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
									cm.events.fire(cm,'checkoutdata',cm.storage['checkoutdata'],source);
			               }
			            });

						}
					});
				});
			}
		}
	};

	cm.userinput = {
		// temp form injection stuff for stripe, but we can make it for anything
		// inject = optional div to injec
		getInput: function (elements,type) {
			type = type || 'unknown';
			var form = document.createElement('form');
			var container = document.createElement('div');
			container.className = 'cm-userinput-container';
			var message = document.createElement('div');
			message.id = 'cm-userinput-message';
			message.innerHTML = '&nbsp;'
			form.className = 'cm-userinput ' + type;

			elements.push({id:'cm-userinput-type', type:'hidden', value:type});

			elements.forEach(function(element) {
				if (element.type !== "submit" && element.type !== "select") {
					var input = document.createElement("input");
					input.type = element.type;
					input.name = element.id;
					input.placeholder = element.placeholder;
					input.id = "cm-userinput-" + type + "-" + element.id;
					if (element.value) {
						input.value = element.value;
					}
					form.appendChild(input);
				} else {
					if (element.type == "select") {
						var input = document.createElement("select");
						input.id = "cm-userinput-" + type + "-" + element.id;
						var codes = Object.keys(element.options);
						input.name = element.id;
						for (var i = 0; i < codes.length; i++) {
						   var option = document.createElement("option");
						   option.value = codes[i];
						   option.text = element.options[codes[i]];
							if (element.value == codes[i]) {
								option.selected = 'selected';
							}
						   input.appendChild(option);
						}
						form.appendChild(input);
					} else {
						var button = document.createElement("button");
						button.type = "submit";
						button.id = "cm-userinput-" + type + "-" + element.id;
						button.name = element.id;
						button.innerHTML = element.text;
						form.appendChild(button);
					}
				}
			});

			container.appendChild(form);
			container.appendChild(message);

			cm.events.add(form,'submit', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var formdata = {};
	         for ( var i = 0; i < form.elements.length; i++ ) {
	            var e = form.elements[i];
	            formdata[e.name] = e.value;
	         }
				cm.events.fire(cm,'userinput',formdata);
			});

			cm.overlay.reveal(container);
		}
	};

	cm.checkout = {
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

		shippingElements: [
			{id: "name", type: "text", placeholder: "Ship to name"},
         {id: "address1", type: "text", placeholder: "Shipping address 1"},
			{id: "address2", type: "text", placeholder: "Shipping address 2"},
			{id: "city", type: "text", placeholder: "City"},
			{id: "state", type: "text", placeholder: "State/Province/Region"},
			{id: "postalcode", type: "text", placeholder: "Postal code"}
		],

		begin: function (options,source) {
			if (cm.embedded) {
				cm.events.fire(cm,'begincheckout',options);
			} else {
				cm.styles.injectCSS(cm.path + 'templates/checkout.css');
				cm.storage['checkoutdata'] = {
					'stripe'   :false,
					'paypal'   :false,
					'shipping' :false,
					'currency' :false
				};

				if (location.protocol !== 'https:' && options.testing !== true) {
					options.stripe = false;
				}
				if (options.shipping) {
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
						cm.checkout.shippingElements.push({id: "country", type: "select", options: cm.checkout.countries, value: selectedCountry});
						if (typeof options.shipping === 'object') {
							cm.checkout.shippingElements.push({id: "shipping-region", type: "select", options: {
								"":"Please select a shipping region",
								"r1":options.shipping.r1,
								"r2":options.shipping.r2
							}});
						}
						cm.checkout.shippingElements.push({id: "shipping-submit", type: "submit", text: "Set shipping info"});
						cm.userinput.getInput(cm.checkout.shippingElements,'getshippingaddress');
						cm.checkout.shippingElements.length = 6;
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
			if (options.stripe && !options.paypal) {
				cm.stripe.generateToken(options.stripe,source);
			} else if (!options.stripe && options.paypal) {
				cm.storage['checkoutdata']['paypal'] = true;
				cm.events.fire(cm,'checkoutdata',cm.storage['checkoutdata'],source);
			} else if (options.stripe && options.paypal) {
				var container = document.createElement("div");
				container.class = "cm-checkout-choose";

				var ppspan = document.createElement("span");
				var stspan = document.createElement("span");

				ppspan.innerHTML = "Pay with PayPal";
				stspan.innerHTML = "Pay with a credit card";

				cm.events.add(ppspan,'click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					cm.storage['checkoutdata']['paypal'] = true;
					cm.events.fire(cm,'checkoutdata',cm.storage['checkoutdata'],source);
					cm.overlay.reveal('redirecting...');
				});

				cm.events.add(stspan,'click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					cm.stripe.generateToken(options.stripe,source);
				});

				container.appendChild(ppspan);
				container.appendChild(stspan);

				cm.overlay.reveal(container);

			} else {
				cm.checkout.showerror();
			}
		},

		showerror: function (type) {
			cm.overlay.reveal('<div class="cm-checkout-error">There are no valid payment types. Please add a payment connection. Check to make sure your site supports SSL (https) if you are using Stripe.</div>');
		}
	}

}()); // END
