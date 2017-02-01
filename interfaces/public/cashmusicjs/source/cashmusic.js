/**
 * The core cashmusic.js file
 *
 * COMPRESSION SETTINGS
 * http://closure-compiler.appspot.com/
 * Closure compiler, SIMPLE MODE
 *
 * @package cashmusic.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list
 * of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or other
 * materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *
 *
 * VERSION: 7
 *
 **/

;window.cashmusic = (function() {
	'use strict';
	var cashmusic;
	if (window.cashmusic != null) {
		// if window.cashmusic exists, we just return the current instance
		cashmusic = window.cashmusic;
	} else {
		// no window.cashmusic, so we build and return an object
		cashmusic = {
			embeds: {
				whitelist: '',
				all: []
			},
			embedded: 		false,
			eventlist: 		{},
			geo: 				null,
			get: 				{},
			lightbox: 		false,
			loaded: 			false,
			name:				'',
			options:			'',
			path:				'',
			scripts: 		[],
			sessionid: 		null, // will set to FALSE on request. this must be NULL here
			soundplayer: 	false,
			storage: 		{},
			templates: 		{},

			_init: function() {
				var cm = window.cashmusic;

				// look for GET string, parse that shit if we can
				cm.get['qs'] =  window.location.search.substring(1);
				cm.get['params'] = false;
				if (cm.get['qs']) {
					cm.get['params'] = {};
					var t;
					var q = cm.get['qs'].split("&");
					for (var i = 0; i < q.length; i++) {
						t = q[i].split('=');
						cm.get['params'][t[0]] = decodeURIComponent(t[1]);
					}
				}

				if (cm.get['params']['debug']) {
					cm.debug.show = true;
				}

				// if we're running in an iframe assume it's an embed (won't do any harm if not)
				if (self !== top) {
					cm._initEmbed();
				} else {
					cm.name = 'main window';
				}

				// start a session
				cm.session.start();

				// check lightbox options
				var imgTest = document.querySelectorAll('a.cashmusic.gallery,div.cashmusic.gallery');
				if (cm.options.indexOf('lightboxvideo') !== -1 || imgTest.length > 0) {
					// load lightbox.js
					cm.loadScript(cm.path+'/lightbox/lightbox.js');
				}

				// look for .cashmusic.soundplayer divs/links
				var soundTest = document.querySelectorAll('a.cashmusic.soundplayer,div.cashmusic.soundplayer');
				if (soundTest.length > 0) {
					cm.loadScript(cm.path+'/soundplayer/soundplayer.js');
				}

				// using messages passed between the request and this script to resize the iframe
				cm.events.add(window,'message',function(e) {
					// make sure the message comes from our embeds OR the main embedding cashmusic.js instance (via origin whitelist)
					if (cm.embeds.whitelist.indexOf(e.origin) !== -1) {
						cm._handleMessage(e);
					}
				});

				// add current domain to whitelist for postmesage calls (regardless of embed or no)
				cm.embeds.whitelist += window.location.href.split('/').slice(0,3).join('/');
				if (cm.get['params']['location']) {
					cm.embeds.whitelist += cm.get['params']['location'].split('/').slice(0,3).join('/');
				}

				if (cm.embedded) {
					cm.loaded = Date.now(); // ready and loaded
					cm._drawQueuedEmbeds();
					cm.debug.store('session id set: ' + cm.sessionid);
					if (cm.debug.show) {
						cm.debug.out('finished initializing',cm);
					}
					// tell em
					cm.events.fire(cm,'ready',cm.loaded);
				} else {
					// look for GET string, parse that shit if we can
					if (cm.get['qs']) {
						if (cm.get['qs'].indexOf('element_id') !== -1 || cm.get['qs'].indexOf('handlequery') !== -1) {
							if (!!(window.history && history.pushState)) {
								// we know this is aimed at us, so we caught it. now remove it.
								history.pushState(null, null, window.location.href.split('?')[0]);
							}
						}
					}

					// create overlay stuff first
					cm.overlay.create();
					// if we don't have a geo response we'll loop and wait a couple
					// seconds before declaring the script ready.
					var l = 0;
					var i = setInterval(function() {
						if ((l < 50) && (!cm.geo || (!cm.sessionid && cm.options.indexOf('standalone') === -1))) {
							l++;
						} else {
							if (cm.sessionid) {
								cm.debug.store('session id set: ' + cm.sessionid);
							} else {
								cm.debug.store('no session. standalone mode.');
							}
							cm.debug.store('geo acquired: ' + cm.geo);
							cm.debug.store('total delay: ' + l*100 + 'ms');
							cm.loaded = Date.now(); // ready and loaded
							// and since we're ready kill the loops
							clearInterval(i);
							cm._drawQueuedEmbeds();
							if (cm.debug.show) {
								cm.debug.out('finished initializing',cm);
							}
							// tell em
							cm.events.fire(cm,'ready',cm.loaded);
						}
					}, 100);
				}
			},

			_drawQueuedEmbeds: function() {
				var cm = window.cashmusic;
				if (typeof cm.storage.elementQueue == 'object') {
					// this means we've got elements waiting for us...do a
					// foreach loop and start embedding them
					cm.storage.elementQueue.forEach(function(args) {
						// we stored the args in our queue...spit them back out
						cm.embed(args[0],args[1],args[2],args[3],args[4],args[5]);
					});
				}
			},

			_initEmbed: function() {
				var cm = window.cashmusic;
				cm.embedded = true; // set this as an embed

				// get main div
				var el = document.querySelector('div.cashmusic.element');
				if (el) {
					cm.storage['embedheight'] = cm.measure.scrollheight(); // store current height
					cm.events.fire(cm,'resize',cm.storage.embedheight); // fire resize event immediately

					// use element classes to identify type and id of element
					var cl = el.className.split(' ');
					cm.events.fire(cm,'identify',[cl[2],cl[3].substr(3)]); // [type, id]

					cm.name = 'element #' + cl[3].substr(3) + ' / ' + cl[2];

					// poll for height and fire resize event if it changes
					window.setInterval(function() {
						var h  = cm.measure.scrollheight();
						if (h != cm.storage.embedheight) {
							cm.storage.embedheight = h;
							cm.events.fire(cm,'resize',h);
						}
					},250);

					// rewrite CSS stuff?
					var cssOverride = cm.getQueryVariable('cssoverride');
					if (cssOverride) {
						cm.styles.injectCSS(cssOverride,true);
					}
				}

				// add an embedded_element input to all forms to tell the platform they're embeds
				var forms = document.getElementsByTagName("form");
				for (var i=0; i<forms.length; i++) {
					var ee=document.createElement("input");
					ee.setAttribute("type","hidden");
					ee.setAttribute("name","embedded_element");
					ee.setAttribute("value","1");
					forms[i].appendChild(ee);
				}
			},

			_handleMessage: function(e) {
				var cm = window.cashmusic;
				var msg = JSON.parse(e.data);
				var md = msg.data;
				var source; // source embed (if from an embed)
				// find the source of the message in our embeds object
				for (var i = 0; i < cm.embeds.all.length; i++) {
					if (cm.embeds.all[i].el.contentWindow === e.source) {
						source = cm.embeds.all[i];
						break;
					}
				}

				// are we asking for a specific element to be targeted?
				var target = false;
				if (md.target) {
					for (var i = 0; i < cm.embeds.all.length; i++) {
						if (cm.embeds.all[i].id == md.target) {
							target = cm.embeds.all[i].el.contentWindow;
							break;
						}
					}
				}

				// now figure out what to do with it
				switch (msg.type) {
					case 'resize':
						source.el.height = md;
						source.el.style.height = md + 'px'; // resize to correct height
						break;
					case 'identify':
						if (source.id == md[1]) { // double-check that id's match
							source.type = md[0]; // set the type. now we have all the infos
						}
						break;
					case 'checkoutdata':
						cm.events.fire(cm,'checkoutdata',md);
						break;
					case 'overlayreveal':
						cm.overlay.reveal(md.innerContent,md.wrapClass);
						cm.events.fire(cm,'overlayopened','');
						break;
					case 'overlayhide':
						cm.overlay.hide();
						cm.events.fire(cm,'overlayhidden','');
						break;
					case 'addoverlaytrigger':
						cm.overlay.addOverlayTrigger(md.content,md.classname,md.ref);
						break;
					case 'injectcss':
						cm.styles.injectCSS(md.css,md.important);
						break;
					case 'addclass':
						cm.styles.addClass(md.el,md.classname);
						break;
					case 'removeclass':
						cm.styles.removeClass(md.el,md.classname);
						break;
					case 'swapclasses':
						cm.styles.swapClasses(md.el,md.oldclass,md.newclass);
						break;
					case 'begincheckout':
						var el = target;
						if (!el) {
							el = e.source;
						}
						if (!cm.checkout) {
							cm.loadScript(cm.path+'/checkout/checkout.js', function() {
								cm.checkout.begin(md,el);
							});
							target = false;
						} else {
							cm.checkout.begin(md,el);
							target = false;
						}
						break;
				}
				if (target) {
					cm.events.fire(cm,msg.type,md,target);
				}
			},

			/*
			 * contentloaded.js by Diego Perini (diego.perini at gmail.com)
			 * http://javascript.nwbox.com/ContentLoaded/
			 * http://javascript.nwbox.com/ContentLoaded/MIT-LICENSE
			 *
			 * modified a little because you know
			 */
			contentLoaded: function(fn) {
				var done = false, top = true,
				doc = window.document, root = doc.documentElement,

				init = function(e) {
					if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
					cashmusic.events.remove((e.type == 'load' ? window : doc),e.type,init);
					if (!done && (done = true)) fn.call(window, e.type || e);
				},

				poll = function() {
					try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
					init('poll');
				};

				if (doc.readyState == 'complete') fn.call(window, 'lazy');
				else {
					if (doc.createEventObject && root.doScroll) {
						try { top = !window.frameElement; } catch(e) { }
						if (top) poll();
					}
					this.events.add(doc,'DOMContentLoaded', init);
					this.events.add(doc,'readystatechange', init);
					this.events.add(doc,'load', init);
				}
			},

			/*
			 * window.cashmusic.embed(string endPoint, string/int elementId, bool lightboxed, bool lightboxTxt)
			 * Generates the embed iFrame code for embedding a given element.
			 * Optional third and fourth parameters allow the element to be
			 * embedded with a lightbox and to customize the text of lightbox
			 * opener link. (default: 'open element')
			 *
			 * The iFrame is embedded at 1px high and sends a postMessage back
			 * to this parent window with its proper height.
			 *
			 * This is called in a script inline as a piece of blocking script — calling it before
			 * contentLoaded because the partial load tells us where to embed each chunk — we find the
			 * last script node and inject the content by it. For dynamic calls you need to specify
			 * a targetNode to serve as the anchor — with the embed chucked immediately after that
			 * element in the DOM.
			 */
			embed: function(elementId, endPoint, lightboxed, lightboxTxt, targetNode, cssOverride) {
				var cm = window.cashmusic;
				// BACKWARDS COMPATIBILITY THING:
				// make endPoint and elementId interchangeable
				if (typeof elementId === 'string') {
					if (elementId.substr(0,1) == 'h' || elementId.substr(0,1) == '/') {
						elementId = [endPoint, endPoint = elementId][0]; // swap values for elementId and endPoint
					}
				}

				// if used non-AJAX we just grab the current place in the doc
				// because we're running as the document is loading in a blocking fashion, the
				// last script element will be the current script asset.
				var allScripts = document.querySelectorAll('script');
				var currentNode = allScripts[allScripts.length - 1];

				if (!cm.loaded) {
					// cheap/fast queue waiting on geo. there's a 2.5s timeout on this. the geo
					// request usually beats page load but this still seems smart and an acceptable
					// balance given the benefit of more data.
					if (typeof cm.storage.elementQueue !== 'object') {
						cm.storage.elementQueue = [];
					}
					if (typeof elementId === 'object') {
						if (!elementId.targetnode) {
							elementId.targetnode = currentNode;
							arguments[0] = elementId;
						}
					} else {
						arguments[4] = currentNode;
					}
					cm.storage.elementQueue.push(arguments);
				} else {
					// Allow for a single object to be passed instead of all arguments
					// object properties should be lowercase versions of the standard arguments, any order
					if (typeof elementId === 'object') {
						lightboxed  = elementId.lightboxed ? elementId.lightboxed : false;
						lightboxTxt = elementId.lightboxtxt ? elementId.lightboxtxt : false;
						targetNode  = elementId.targetnode ? elementId.targetnode : false;
						cssOverride = elementId.cssoverride ? elementId.cssoverride : false;
						endPoint    = elementId.endpoint ? elementId.endpoint : false;
						elementId   = elementId.elementid ? elementId.elementid : false;
					}
					if (typeof targetNode === 'string') {
						// for AJAX, specify target node: '#id', '#id .class', etc. NEEDS to be specific
						currentNode = document.querySelector(targetNode);
					} else {
						currentNode = targetNode;
					}

					// if no endpoint is specified, let's try the default location,
					// relative to the cashmusic.js location
					if (!endPoint) {
						endPoint = cm.path;
					}

					// make the iframe
					var iframe = cm.buildEmbedIframe(endPoint,elementId,cssOverride,(lightboxed) ? 'lightbox=1' : false);

					// be nice neighbors. if we can't find currentNode, don't do the rest or pitch errors. silently fail.
					if (currentNode) {
						if (lightboxed) {
							// create a div to contain the overlay link
							var embedNode = document.createElement('span');
							embedNode.className = 'cashmusic embed link';

							// open in a lightbox with a link in the target div
							if (!lightboxTxt) {lightboxTxt = 'open element';}
							cm.overlay.create(function() {
								var a = document.createElement('a');
									a.href = '';
									a.target = '_blank';
									a.innerHTML = lightboxTxt;
								embedNode.appendChild(a);
								currentNode.parentNode.insertBefore(embedNode,currentNode);
								(function() {
									cm.events.add(a,'click',function(e) {
										cm.overlay.reveal(iframe);
										e.preventDefault();
										return false;
									});
								})();
							});

						} else {
							// put the iframe in place
							currentNode.parentNode.insertBefore(iframe,currentNode);
						}
					}
				}
			},

			buildEmbedIframe: function(endpoint,id,cssoverride,querystring) {
				var cm = window.cashmusic;
				var embedURL = endpoint.replace(/\/$/, '') + '/request/embed/' + id + '?location=' + encodeURIComponent(window.location.href);
				if (cm.geo) {
					embedURL += '&geo=' + encodeURIComponent(cm.geo);
				}
				if (cssoverride) {
					embedURL += '&cssoverride=' + encodeURIComponent(cssoverride);
				}
				if (querystring) {
					embedURL += '&' + querystring;
				}
				if (cm.get['params'] && (''+querystring).indexOf('lightbox=1') === -1) {
					if (cm.get['params']['element_id'] == id || cm.get['params']['handlequery']) {
						embedURL += '&' + cm.get['qs'];
					}
				}
				if (cm.sessionid) {
					embedURL += '&session_id=' + cm.sessionid;
				}
				if (cm.debug.show) {
					embedURL += '&debug=1';
				}

				var iframe = document.createElement('iframe');
					iframe.src = embedURL;
					iframe.id = 'cm-' + new Date().getTime(); // prevent Safari from using old data.
					iframe.className = 'cashmusic embed';
					iframe.style.width = '100%';
					iframe.style.height = '0'; // if not explicitly set the scrollheight of the document will be wrong
					iframe.style.border = '0';
					iframe.style.overflow = 'hidden'; // important for overlays, which flicker scrollbars on open
					iframe.scrolling = 'no'; // programming

				var origin = embedURL.split('/').slice(0,3).join('/');
				if (cm.embeds.whitelist.indexOf(origin) === -1) {
					cm.embeds.whitelist = cm.embeds.whitelist + origin;
				}
				cm.embeds.all.push({el:iframe,id:id,type:''});

				cm.debug.store('building iframe for element #' + id);

				return iframe;
			},

			getTemplate: function(templateName,successCallback) {
				var cm = window.cashmusic;
				var templates = cm.templates;
				if (templates[templateName] !== undefined) {
					successCallback(templates[templateName]);
				} else {
					// get the template
					this.ajax.jsonp(
						cm.path + '/templates/' + templateName + '.js',
						'callback',
						function(json) {
							templates[templateName] = json.template;
							successCallback(json.template);
						},
						'cashmusic' + templateName + 'Callback'
					);


					// check for existence of the CSS file and if not found, include it
					var test = document.querySelectorAll('link[href="' + cm.path + '/templates/' + templateName + '.css' + '"]');
					if (!test.length ) { // if nothing found
						cm.styles.injectCSS(cm.path + '/templates/' + templateName + '.css');
					}
				}
			},

			/*
			 *	Use standard event footprint
			 */
			addEventListener: function(eventName, callback) {
				var cm = window.cashmusic;
				if(!cm.eventlist.hasOwnProperty(eventName)) {
					cm.eventlist[eventName] = [];
				}
				cm.eventlist[eventName].push(callback);
			},

			/*
			 *	Use standard event footprint
			 */
			removeEventListener: function(eventName, callback) {
				var cm = window.cashmusic;
				if(cm.eventlist.hasOwnProperty(eventName)) {
					var idx = cm.eventlist[eventName].indexOf(callback);
					if(idx != -1) {
						cm.eventlist[eventName].splice(idx, 1);
					}
				}
			},

			/*
			 *	Use standard event footprint
			 */
			dispatchEvent: function(e) {
				var cm = window.cashmusic;
				if(cm.eventlist.hasOwnProperty(e.type)) {
					var i;
					for(i = 0; i < cm.eventlist[e.type].length; i++) {
						if (cm.eventlist[e.type][i]) {
							cm.eventlist[e.type][i](e);
						}
					}
				}
			},

			// stolen from jQuery
			loadScript: function(url,callback) {
				var cm = window.cashmusic;
				if (cm.scripts.indexOf(url) > -1) {
					if (typeof callback === 'function') {
						callback();
					}
				} else {
					cm.scripts.push(url);
					var head = document.getElementsByTagName('head')[0] || document.documentElement;
					var script = document.createElement('script');
					script.src = url;

					// Handle Script loading
					var done = false;

					// Attach handlers for all browsers
					script.onload = script.onreadystatechange = function() {
						if ( !done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") ) {
							done = true;
							if (typeof callback === 'function') {
								callback();
							}

							// Handle memory leak in IE
							script.onload = script.onreadystatechange = null;
							if (head && script.parentNode) {head.removeChild(script);}
						}
					};
					head.insertBefore( script, head.firstChild );
				}
				// log it
				if (cm.debug.show) {
					if (!cm.loaded) {
						cm.debug.store('loaded script: ' + url);
					} else {
						cm.debug.out('loaded script: ' + url);
					}
				}
			},

			// found: http://css-tricks.com/snippets/javascript/get-url-variables/
			getQueryVariable: function(v) {
				var query = window.location.search.substring(1);
				var vars = query.split("&");
				for (var i=0;i<vars.length;i++) {
					var pair = vars[i].split("=");
					if(pair[0] == v){
						return decodeURIComponent(pair[1]);
					}
				}
				return(false);
			},

			/***************************************************************************************
 			 *
 			 * window.cashmusic.debug (object)
 			 * Store debug messages for grouping OR dump a message / all stored messages
 			 *
 			 * PUBLIC-ISH FUNCTIONS
 			 * window.cashmusic.debug.store(string msg,optional object o)
 			 * window.cashmusic.debug.out(string msg,optional object o)
 			 *
 			 ***************************************************************************************/
			debug: {
				show: false,

				store: function(msg,o) {
					// making a debug message queue
					var cm = window.cashmusic;
					if (!cm.storage.debug) {
						cm.storage.debug = [];
					}
					cm.storage.debug.push({"msg":msg,"o":o});
				},

				out: function(msg,o) {
					var cm = window.cashmusic;
					if (!cm.storage.debug) {
						// no queue: just spit out the message and (optionally) object
						if (o) {
							console.log('%cⓃ ' + cm.name + ': ' + msg + ' %o', 'color: #FF00FF;', o);
						} else {
							console.log('%cⓃ ' + cm.name + ': ' + msg, 'color: #FF00FF;');
						}
					} else {
						// queue: run through all of it as part of a collapsed group
						console.groupCollapsed('%cⓃ ' + cm.name + ': ' + msg, 'color: #FF00FF;');
						if (o) {
							console.log('   attachment: %o', o);
						}
						cm.storage.debug.forEach(function(d) {
							if (d.o) {
								console.log('   ' + d.msg + ' %o', d.o);
							} else {
								console.log('   ' + d.msg);
							}
						});
						console.groupEnd();
						// now clear the debug queue
						delete cm.storage.debug;
					}
				}
			},

			/***************************************************************************************
			 *
			 * window.cashmusic.ajax (object)
			 * Object wrapping XHR calls cross-browser and providing form encoding for POST
			 *
			 * PUBLIC-ISH FUNCTIONS
			 * window.cashmusic.ajax.send(string url, string postString, function successCallback)
			 * window.cashmusic.ajax.encodeForm(object form)
			 *
			 ***************************************************************************************/
			ajax: {
				/*
				 * window.cashmusic.ajax.getXHR()
				 * Tests for the proper XHR object type and returns the appropriate
				 * object type for the current browser using a try/catch block. If
				 * no viable objects are found it returns false. But we should make
				 * fun of that browser, because it sucks.
				 */
				getXHR: function() {
					try	{
						return new XMLHttpRequest();
					} catch(e) {
						try {
							return new ActiveXObject('Msxml2.XMLHTTP');
						} catch(er) {
							try {
								return new ActiveXObject('Microsoft.XMLHTTP');
							} catch(err) {
								return false;
							}
						}
					}
				},

				/*
				 * window.cashmusic.ajax.send(string url, string postString, function successCallback)
				 * Do a POST or GET request via XHR/AJAX. Passing a postString will
				 * force a POST request, whereas passing false will send a GET.
				 */
				send: function(url,postString,successCallback,failureCallback) {
					var cm = window.cashmusic;
					var method = 'POST';
					if (!postString) {
						method = 'GET';
						postString = null;
						if (cm.sessionid) {
							if (url.indexOf('?') === -1) {
								url += '?session_id=' + cm.sessionid;
							} else {
								url += '&session_id=' + cm.sessionid;
							}
						}
					} else {
						if (cm.sessionid) {
							postString += '&session_id=' + cm.sessionid;
						}
					}
					var xhr = this.getXHR();
					if (xhr) {
						xhr.open(method,url,true);
						xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
						if (method == 'POST') {
							xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
						}
						if (typeof successCallback == 'function') {
							xhr.onreadystatechange = function() {
								if (xhr.readyState === 4) {
									if (xhr.status === 200) {
										successCallback(xhr.responseText);
									} else {
										if (typeof failureCallback === 'function') {
											failureCallback(xhr.responseText);
										}
									}
								}
							};
						}
						xhr.send(postString);
					}
				},

				jsonp: function(url,method,callback,forceCallbackName) {
					// lifted from Oscar Godson here:
					// http://oscargodson.com/posts/unmasking-jsonp.html

					// added the forceCallbackName bits, and callback queing/stacking

					url = url || '';
					method = method || '';
					callback = callback || function(){};
					forceCallbackName = forceCallbackName || false;

					if(typeof method == 'function'){
						callback = method;
						method = 'callback';
					}

					if (forceCallbackName) {
						// this is weird. it looks to see if the callback is already defined
						// if it is it means we hit a race condition loading the template and
						// handling the callback.
						var generatedFunction = forceCallbackName;
						var oldCallback = (function(){});
						if (typeof window[generatedFunction] == 'function') {
							// we grab the old callback, create a new closure for it, and call
							// it in our new callback — nests as deep as it needs to go, calling
							// every callback in reverse order
							oldCallback = window[generatedFunction];
						}
					} else {
						var generatedFunction = 'jsonp'+Math.round(Math.random()*1000001);
					}

					window[generatedFunction] = function(json){
						callback(json);
						if (!forceCallbackName) {
							delete window[generatedFunction];
						} else {
							// here we start the weird loop down through all the defined
							// callbacks. if no callbacks were defined oldCallback is an
							// empty function so it does nothing.
							oldCallback(json);
						}
					};

					if (url.indexOf('?') === -1) {url = url+'?';} else {url = url+'&';}

					var s = document.createElement('script');
					s.setAttribute('src', url+method+'='+generatedFunction);
					document.getElementsByTagName('head')[0].appendChild(s);
				},

				/*
				 * window.cashmusic.ajax.encodeForm(object form)
				 * Takes a form object returned by a document.getElementBy... call
				 * and turns it into a querystring to be used with a GET or POST call.
				 */
				encodeForm: function(form) {
					if (typeof form !== 'object') {
						return false;
					}
					var querystring = '';
					form = form.elements || form; //double check for elements node-list
					for (var i=0;i<form.length;i++) {
						if (form[i].type === 'checkbox' || form[i].type === 'radio') {
							if (form[i].checked) {
								querystring += (querystring.length ? '&' : '') + form[i].name + '=' + form[i].value;
							}
							continue;
						}
						querystring += (querystring.length ? '&' : '') + form[i].name +'='+ form[i].value;
					}
					return encodeURI(querystring);
				},

				getHeaderForURL: function(url,header,callback) {
					var xhr = this.getXHR();
					xhr.open('HEAD', url);
					xhr.onreadystatechange = function() {
						if (this.readyState == this.DONE) {
							callback(this.getResponseHeader(header));
						}
					};
					xhr.send();
				}
			},

			/***************************************************************************************
			 *
			 * window.cashmusic.events (object)
			 * Add, remove, and fire events
			 *
			 * PUBLIC-ISH FUNCTIONS
			 * window.cashmusic.events.add(object obj, string type, function fn)
			 * window.cashmusic.events.remove(object obj, string type, function fn)
			 * window.cashmusic.events.fire(object obj, string type, object/any data)
			 *
			 ***************************************************************************************/
			events: {
				// Thanks, John Resig!
				// http://ejohn.org/blog/flexible-javascript-events/
				add: function(obj,type,fn) {
					if (obj.attachEvent) {
						obj['e'+type+fn] = fn;
						obj[type+fn] = function(){obj['e'+type+fn]( window.event );}
						obj.attachEvent( 'on'+type, obj[type+fn] );
					} else {
						obj.addEventListener( type, fn, false );
					}
				},

				// Thanks, John Resig!
				// http://ejohn.org/blog/flexible-javascript-events/
				remove: function(obj,type,fn) {
					if (obj.detachEvent) {
						obj.detachEvent( 'on'+type, obj[type+fn] );
						obj[type+fn] = null;
					} else {
						obj.removeEventListener( type, fn, false );
					}
				},

				// added the fourth "source" parameter
				fire: function(obj,type,data,source) {
					var cm = window.cashmusic;
					if (source) {
						// source window found, so push to it via postMessage
						source.postMessage(JSON.stringify({
							'type': type,
							'data': data
						}),'*');
					}
					// fire the event locally no matter what
					if (document.dispatchEvent){
						// standard
						var e = document.createEvent('CustomEvent');
   					e.initCustomEvent(type, false, false, data);
   					obj.dispatchEvent(e);
					} else {
						// dispatch for IE < 9
						var e = document.createEventObject();
						e.detail = data;
						obj.fireEvent('on'+type,e);
					}
					if (cm.embedded) {
						cm.events.relay(type,data);
					}
					// log it
					if (cm.debug.show) {
						if (!cm.loaded) {
							cm.debug.store('firing ' + type + ' event.',data);
						} else {
							cm.debug.out('firing ' + type + ' event.',data);
						}
					}
				},

				relay: function(type,data) {
					window.parent.postMessage(JSON.stringify({
						'type': type,
						'data': data
					}),'*');
				}
			},

			/***************************************************************************************
			 *
			 * window.cashmusic.session (object)
			 * Test session things because Safari is garbage
			 *
			 ***************************************************************************************/
			session: {
				start: function() {
					var cm = window.cashmusic;
					if (!cm.sessionid && cm.options.indexOf('standalone') === -1) {
						if (cm.get['params']['session_id']) {
							cm.sessionid = cm.get['params']['session_id'];
						} else {
							var id = cm.session.getid(window.location.href.split('/').slice(0,3).join('/'));
						}
					}
				},

				setid: function(id) {
					var cm = window.cashmusic;
					var session = JSON.parse(id);
					// first set the local session id
					cm.sessionid = session.id;
					// now try making it persistent
					if (!cm.embedded) {
						try {
							var sessions = localStorage.getItem('sessions');
							if (!sessions) {
								sessions = {};
							} else {
								sessions = JSON.parse(sessions);
							}
							sessions[window.location.href.split('/').slice(0,3).join('/')] = {
								"id":session.id,
								"expiration":session.expiration
							};
							localStorage.setItem('sessions', JSON.stringify(sessions));
						} catch (e) {}
					}
				},

				getid: function(key) {
					var cm = window.cashmusic;
					if (!cm.sessionid) {
						// first pass, check for a session_id=x GET param
						if (cm.get['params']['session_id']) {
							cm.sessionid = cm.get['params']['session_id'];
						}
					}
					if (!cm.sessionid && !cm.embedded) {
						// okay so no GET param. look in localstorage
						// skip this for embeds — we key on URL and in embeds they are all the same, so...
						// ...we'll run into overlap this way. all embeds should have GET params.
						var sessions = false;
						try {
							sessions = localStorage.getItem('sessions'); // may not have access, so use a try
						} catch (e) {
							sessions = false;
						}
						if (sessions) {
							sessions = JSON.parse(sessions);
							if (sessions[key]) {
								if ((sessions[key].expiration) > Math.floor(new Date().getTime()/1000)) {
									cm.sessionid = sessions[key].id;
								} else {
									delete sessions[key];
									localStorage.setItem('sessions', JSON.stringify(sessions));
								}
							}
						}
					}
					if (cm.sessionid === null && !cm.embedded && cm.options.indexOf('standalone') === -1) {
						// before anything else: change cm.sessionid to FALSE to signify that we're requesting
						// a new session id. that will stop this block from executing a second time
						cm.sessionid = false;
						// okay so no GET and no localstorage. ask the serversessionstart
						var endpoint = cm.path.replace('public','api')+'/verbose/system/startjssession';
						endpoint += '?ts=' + new Date().getTime();
						// fire off the ajax call
						cm.ajax.send(
							endpoint,
							false,
							function(r) {
								if (r) {
									var rp = JSON.parse(r);
									cm.session.setid(rp.payload);
									cm.events.fire(cm,'sessionstarted',rp.payload);
								}
							},
							function(r) {
								cm.options += ' standalone';
							}
						);
					}
					return cm.sessionid;
				}
			},

			/***************************************************************************************
			 *
			 * window.cashmusic.measure (object)
			 * Basic window/element measurements
			 *
			 * PUBLIC-ISH FUNCTIONS
			 * window.cashmusic.measure.viewport()
			 * window.cashmusic.measure.getClickPosition(event e)
			 *
			 ***************************************************************************************/
			measure: {
				viewport: function() {
					/*
						x: viewport width
						y: viewport height
					*/
					return {
						x: window.innerWidth || document.body.offsetWidth || 0,
						y: window.innerHeight || document.body.offsetHeight || 0
					};
				},

				scrollheight: function() {
					// returns scrollable content height
					var db=document.body;
					var de=document.documentElement;
					return Math.max(db.scrollHeight,de.scrollHeight,db.offsetHeight,de.offsetHeight,db.clientHeight,de.clientHeight);
				}
			},

			/***************************************************************************************
			 *
			 * window.cashmusic.overlay (object)
			 * Building the actual lightbox bits
			 *
			 * PUBLIC-ISH FUNCTIONS
			 * window.cashmusic.overlay.create(function callback)
			 * window.cashmusic.overlay.hide()
			 * window.cashmusic.overlay.reveal(string/object innerContent, string wrapClass)
			 *
			 ***************************************************************************************/
			overlay: {
				content: false,
				close: false,
				callbacks: [],

				create: function(callback) {
					var cm = window.cashmusic;
					var self = cm.overlay;
					var move = false;

					cm.styles.injectCSS(cm.path + '/templates/overlay.css');

					self.content = document.createElement('div');
					self.content.className = 'cm-overlay';

					self.close = document.createElement('div');
					self.close.className = 'cm-close';

					cm.events.add(window,'keyup', function(e) {
						if (e.keyCode == 27) {
							if (self.content.parentNode == document.body) {
								self.hide();
							}
						}
					});
					cm.events.add(self.close,'click', function(e) {
						if (self.content.parentNode == document.body) {
							self.hide();
						}
					});
					/*
					cm.events.add(self.bg,'click', function(e) {
						if(e.target === this) {
							self.hide();
						}
					});
					*/
					if (typeof callback === 'function') {
						callback();
					}
				},

				hide: function() {
					var cm = window.cashmusic;
					var self = cm.overlay;
					var db = document.body;
					if (cm.embedded) {
						cm.events.fire(cm,'overlayhide');
					} else {
						self.content.style.opacity = 0;
						cm.events.fire(cm,'overlayclosed',''); // tell em

						//self.content.innerHTML = '';
						while (self.content.firstChild) {
							self.content.removeChild(self.content.firstChild);
						}
						db.removeChild(self.close);
						db.removeChild(self.content);

						// reveal any (if) overlay triggers
						var t = document.querySelectorAll('.cm-overlaytrigger');
						if (t.length > 0) {
							for (var i = 0, len = t.length; i < len; i++) {
								t[i].style.visibility = 'visible';
							}
						}

						// reenable body scrolling
						cm.styles.removeClass(document.documentElement,'cm-noscroll');
					}
				},

				reveal: function(innerContent,wrapClass) {
					// add the correct content to the content div
					var cm = window.cashmusic;
					var self = cm.overlay;
					var db = document.body;
					if (cm.embedded) {
						cm.events.fire(cm,'overlayreveal',{"innerContent":innerContent,"wrapClass":wrapClass});
					} else {
						// if the overlay is already visible, kill the contents first
						if (self.content.style.opacity == 1) {
							self.content.innerHTML = '';
						}
						var positioning = document.createElement('div');
						positioning.className = 'cm-position';
						var alert = document.createElement('div');
						if (wrapClass) {
							alert.className = wrapClass;
						} else {
							alert.className = 'cm-element';
						}
						if (typeof innerContent === 'string') {
							alert.innerHTML = innerContent;
						} else {
							if (innerContent.endpoint && innerContent.element) {
								// make the iframe
								var s = '';
								if (cm.sessionid) {
									s = '&session_id=' + cm.sessionid;
								}
								var iframe = cm.buildEmbedIframe(innerContent.endpoint,innerContent.element,false,'lightbox=1&state='+innerContent.state+s);
								alert.appendChild(iframe);
							} else {
								alert.appendChild(innerContent);
							}
						}
						positioning.appendChild(alert);
						self.content.appendChild(positioning);

						// disable body scrolling
						if(!cm.styles.hasClass(document.documentElement,'cm-noscroll')){cm.styles.addClass(document.documentElement,'cm-noscroll');}

						// if not already showing, go!
						if (self.content.style.opacity != 1) {
							self.content.style.opacity = 0;
							db.appendChild(self.content);
							db.appendChild(self.close);
							// force style refresh/redraw on element
							window.getComputedStyle(self.content).opacity;
							// initiate fade-in
							self.content.style.opacity = 1;
						}
					}
				},

				addOverlayTrigger: function(content,classname,ref) {
					var cm = window.cashmusic;
					var self = cm.overlay;
					var db = document.body;
					if (cm.embedded) {
						cm.events.fire(cm,'addoverlaytrigger',{
							"content":content,
							"classname":classname,
							"ref":ref
						});
					} else {
						var el = document.createElement('div');
						el.className = classname.toString() + ' cm-overlaytrigger';
						cm.events.add(el,'click',function(e) {
							cm.overlay.reveal(content);
							this.style.visibility = 'hidden';
							e.preventDefault();
							return false;
						});
						db.appendChild(el);
						cm.storage[ref] = el;
						cm.events.fire(cm,'triggeradded',ref);
					}
				}
			},

			/***************************************************************************************
			 *
			 * window.cashmusic.styles (object)
			 * Building the actual lightbox bits
			 *
			 * PUBLIC-ISH FUNCTIONS
			 * window.cashmusic.styles.addClass(HTML element el, string classname)
			 * window.cashmusic.styles.hasClass(HTML element el, string classname)
			 * window.cashmusic.styles.injectCSS(string css, boolean important)
			 * window.cashmusic.styles.removeClass(HTML element el, string classname)
			 * window.cashmusic.styles.swapClasses(HTML element el, string oldclass, string newclass)
			 *
			 ***************************************************************************************/
			styles: {
				resolveElement: function(el) {
					if (typeof el === 'string') {
						if (el.substr(0,8) == 'storage:') {
							return window.cashmusic.storage[el.substr(8)];
						} else {
							return document.querySelector(el);
						}
					} else {
						return el;
					}
				},

				addClass: function(el,classname,top) {
					var cm = window.cashmusic;
					if (top && cm.embedded) {
						cm.events.fire(cm,'addclass',{
							"el":el,
							"classname":classname
						});
					} else {
						el = cm.styles.resolveElement(el);
						if (el) {
							el.className = el.className + ' ' + classname;
						}
					}
				},

				hasClass: function(el,classname) {
					// borrowed the idea from http://stackoverflow.com/a/5898748/1964808
					return (' ' + el.className + ' ').indexOf(' ' + classname + ' ') > -1;
				},

				injectCSS: function(css,important,mainwindow) {
					if (mainwindow === undefined) {
						mainwindow = false;
					}
					var cm = window.cashmusic;
					if (mainwindow && cm.embedded) {
						cm.events.fire(cm,'injectcss',{
							"css":css,
							"important":important
						});
					} else {
						var head = document.getElementsByTagName('head')[0] || document.documentElement;
						if (css.substr(0,4) == 'http') {
							// if css starts with "http" treat it as an external stylesheet
							var el = document.createElement('link');
							el.rel = 'stylesheet';
							el.href = css;
						} else {
							// without the "http" wrap css with a style tag
							var el = document.createElement('style');
							el.innerHTML = css;
						}
						el.type = 'text/css';

						if (important) {
							// important means we don't need to write !important all over the place
							// allows for overrides, etc
							head.appendChild(el);
						} else {
							// by injecting the css BEFORE any other style elements it means all
							// styles can be manually overridden with ease — no !important or similar,
							// no external files, etc...
							head.insertBefore(el, head.firstChild);
						}
					}
				},

				removeClass: function(el,classname,top) {
					var cm = window.cashmusic;
					if (top && cm.embedded) {
						cm.events.fire(cm,'removeclass',{
							"el":el,
							"classname":classname
						});
					} else {
						// extra spaces allow for consistent matching.
						// the "replace(/^\s+/, '').replace(/\s+$/, '')" stuff is because .trim() isn't supported on ie8
						el = cm.styles.resolveElement(el);
						if (el) {
							el.className = ((' ' + el.className + ' ').replace(' ' + classname + ' ',' ')).replace(/^\s+/, '').replace(/\s+$/, '');
						}
					}
				},

				swapClasses: function(el,oldclass,newclass,top) {
					var cm = window.cashmusic;
					if (top && cm.embedded) {
						cm.events.fire(cm,'swapclasses',{
							"el":el,
							"oldclass":oldclass,
							"newclass":newclass
						});
					} else {
						// add spaces to ensure we're not doing a partial find/replace,
						// trim off extra spaces before setting
						el = cm.styles.resolveElement(el);
						if (el) {
							el.className = ((' ' + el.className + ' ').replace(' ' + oldclass + ' ',' ' + newclass + ' ')).replace(/^\s+/, '').replace(/\s+$/, '');
						}
					}
				}
			},
		};

		/*
		 *	Post-definition (runtime) calls. For the _init() function to "auto" load...
		 */

		// set path and get all script options
		// file location and path
		var s = document.querySelector('script[src$="cashmusic.js"]');
		if (s) {
			// chop off last 13 characters for '/cashmusic.js' -- not just a replace in case
			// a directory is actually named 'cashmusic.js'
			cashmusic.path = s.src.substr(0,s.src.length-13);
		}
		// get and store options
		cashmusic.options = String(s.getAttribute('data-options'));

		if (self === top) {
			// start on geo-ip data early (only if not embedded)
			cashmusic.ajax.getHeaderForURL('https://javascript-cashmusic.netdna-ssl.com/cashmusic.js','GeoIp-Data',function(h) {
				cashmusic.geo = h;
			});
		}

		var checkEmbeds = function() {
			// check for element definition in script data-element
			var scripts = document.querySelectorAll('script[src$="cashmusic.js"]');
			if (typeof scripts == 'object') {
				var sA = Array.prototype.slice.call(scripts);
				sA.forEach(function(s) {
					var el = s.getAttribute('data-element');
					if (el) {
						cashmusic.embed({
							"elementid": el,
							"targetnode": s
						});
					}
				});
			}
		}

		var init = function(){
			// function traps cashmusic in a closure
			if (cashmusic.options.indexOf('lazy') !== -1) {
				// lazy mode...chill for a second
				setTimeout(function() {
					cashmusic._init(cashmusic);
					checkEmbeds();
				}, 1000);
			} else {
				cashmusic._init(cashmusic);
				checkEmbeds();
			}
		};
		cashmusic.contentLoaded(init); // loads only after the page is complete
	}

	/*
	 *	return the main object in case it's called into a different scope
	 */
	return cashmusic;

}());
