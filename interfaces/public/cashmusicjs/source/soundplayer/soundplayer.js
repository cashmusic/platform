/**
 * Front-end/UI for SoundManager2, global progress events and animations
 *
 * COMPRESSION SETTINGS
 * http://closure-compiler.appspot.com/
 * Closure compiler, SIMPLE MODE
 *
 * @package cashmusic.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2014, CASH Music
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
 **/

(function() {
	'use strict';
	var cm = window.cashmusic;

	// Thanks Kirupa Chinnathambi!
	// http://www.kirupa.com/html5/getting_mouse_click_position.htm
	cm.measure.getClickPosition = function(e) {
		var t = (e.currentTarget) ? e.currentTarget : e.srcElement;
		var parentPosition = cm.measure.getPosition(t);
		var xPosition = e.clientX - parentPosition.x;
		var yPosition = e.clientY - parentPosition.y;
		var percent = xPosition / t.clientWidth;
		return { x: xPosition, y: yPosition, percentage: percent };
	};

	// Thanks Kirupa Chinnathambi!
	// http://www.kirupa.com/html5/getting_mouse_click_position.htm
	cm.measure.getPosition = function(element) {
		var xPosition = 0;
		var yPosition = 0;

		while (element) {
			xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
			yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
			element = element.offsetParent;
		}
		return { x: xPosition, y: yPosition };
	};

	// we need indexOf for the sake of figuring out if an id is part of a
	// larger playlist. IE8 is an asshole. so here goes.
	// http://stackoverflow.com/a/1181586/1964808
	if(!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(needle) {
			for(var i = 0; i < this.length; i++) {
				if(this[i] === needle) {
					return i;
				}
			}
			return -1;
		};
	}

	/***************************************************************************************
	 *
	 * window.cashmusic.soundplayer (object)
	 * SoundManager2 front and song-based tweening
	 *
	 * PUBLIC-ISH FUNCTIONS
	 * window.cashmusic.soundplayer
	 *
	 ***************************************************************************************/
	var sp; // this declaration is down here so we don't forget scope when it's set in _init
	cm.soundplayer = {
		playlist: false,
		playlists: {},
		sound: false,
		lastTimeEvent: '0:00',
		styleDivs: null,
		tweenDivs: null,

		/*
		 * window.cashmusic.soundplayer._init()
		 * A defered call to _init() will set up the object once SM2 is loaded below
		 */
		_init: function() {
			sp = cm.soundplayer;
			// get style/tween divs for caching — we update these on every play event in case new
			// elements are added to the DOM, but caching allows us to skip the querySelectorAll on
			// every tween/style refresh
			sp.styleDivs = document.querySelectorAll('*.cashmusic.setstyles');
			sp.tweenDivs = document.querySelectorAll('*.cashmusic.tween');

			// build any actualy players using the soundplayer.html / soundplayer.css
			var playlistdivs = document.querySelectorAll('div.cashmusic.soundplayer.playlist');
			len = playlistdivs.length;
			if (len > 0) {
				for (var i=0;i<len;i++) {
					var d = playlistdivs[i];
					var pl = JSON.parse(d.getAttribute('data-playlist'));
					pl = sp._formatPlaylist(pl,d.id,i);
					d.id = d.id ? d.id : pl.id;

					var soundlinks = document.querySelectorAll(
						'#' + d.id + ' ' + 'a[href$=".mp3"],' +
						'#' + d.id + ' ' + 'a[href$=".MP3"],' +
						'#' + d.id + ' ' + 'a[href$=".ogg"],' +
						'#' + d.id + ' ' + 'a[href$=".OGG"],' +
						'#' + d.id + ' ' + 'a[href$=".m4a"],' +
						'#' + d.id + ' ' + 'a[href$=".M4A"],' +
						'#' + d.id + ' ' + 'a[href$=".wav"],' +
						'#' + d.id + ' ' + 'a[href$=".WAV"]'
					);
					var sllen = soundlinks.length;
					for (var n=0;n<sllen;n++) {
						var sl = soundlinks[n];
						pl.tracks.push(sp._formatTrack(sl,pl.id));
						sl.parentNode.removeChild(sl);
					}

					sp.addPlaylist(pl,true); // add the playlist to the system (the true skips reformatting the pl)
					sp.drawPlayer(d,pl); // draw player UI and insert it into the target div (d)
				}
			}

			// look for .cashmusic.soundplayer toggle/inline links
			var inlineLinks = document.querySelectorAll('*.cashmusic.soundplayer.playstop,a.cashmusic.soundplayer.inline');
			var len = inlineLinks.length;
			if (len > 0) {
				cm.styles.injectCSS(
					'a.cashmusic.soundplayer.inline.stopped:after{content: " [▸]";}' +
					'a.cashmusic.soundplayer.inline.playing:after{content: " [▪]";}'
				);

				for (var i=0;i<len;i++) {
					var a = inlineLinks[i];
					cm.styles.addClass(a,'stopped');
					if (!cm.styles.hasClass(a,'playstop')) {
						soundManager.createSound({
							id: a.href,
							url: a.href
						});
					} else {
						var soundid = a.getAttribute('data-soundid');
						if (!soundManager.getSoundById(soundid)) {
							soundManager.createSound({
								id: soundid,
								url: soundid
							});
						}
					}
					cm.events.add(a,'click',function(e) {
						if (cm.styles.hasClass(a,'playstop')) {
							var s = soundManager.getSoundById(a.getAttribute('data-soundid'));
						} else {
							var s = soundManager.getSoundById(a.href);
						}
						if (s) {
							sp.toggle(s.id,true);

							e.returnValue = false;
							if(e.preventDefault) e.preventDefault();
							return false;
						}
					});
				}
			}

			// look for .cashmusic.soundplayer play/pause toggles
			var playpause = document.querySelectorAll('*.cashmusic.soundplayer.playpause');
			len = playpause.length;
			if (len > 0) {
				for (var i=0;i<len;i++) {
					var pp = playpause[i];

					cm.styles.addClass(pp,'paused');
					var soundid = pp.getAttribute('data-soundid');
					var playerid = pp.getAttribute('data-playerid');
					if (playerid) {
						cm.events.add(pp,'click',function(e) {
							var t = (e.currentTarget) ? e.currentTarget : e.srcElement;
							sp.togglePlaylist(t.getAttribute('data-playerid'));
							e.returnValue = false;
							if(e.preventDefault) e.preventDefault();
							return false;
						});
					} else {
						if (!soundManager.getSoundById(soundid)) {
							soundManager.createSound({
								id: soundid,
								url: soundid
							});
						}
						cm.events.add(pp,'click',function(e) {
							var t = (e.currentTarget) ? e.currentTarget : e.srcElement;
							sp.toggle(t.getAttribute('data-soundid'));
							e.returnValue = false;
							if(e.preventDefault) e.preventDefault();
							return false;
						});
					}
				}
			}
		},



		/***************************************************************************************
		 *
		 * PUBLIC-ISH FUNCTIONS
		 * Easily accessible wrappers for SM2 interaction. We also mock/replace some functions
		 * to guarantee state and make playlist management easier. The idea being that we only
		 * want one sound playing at a given time, so we force that behavior by managing
		 * window.cashmusic.soundplayer.playlist and window.cashmusic.soundplayer.sound and
		 * only calling pause/play in central functions.
		 *
		 ***************************************************************************************/

		pause: function() {
			if (sp.sound) {
				sp.sound.pause();
			}
		},

		play: function() {
			if (sp.sound) {
				sp.sound.play();
			}
		},

		resume: function() {
			if (sp.sound) {
				if (sp.sound.paused) {
					sp.sound.resume();
				}
			}
		},

		seek: function(position,playlistId) {
			if (playlistId) {
				if (!sp.playlist) {
					return false;
				} else {
					if (playlistId !== sp.playlist.id) return false;
				}
				sp.sound.setPosition(Math.floor(position * sp.sound.duration));
			}
		},

		stop: function() {
			if (sp.sound) {
				sp.pause();
				sp.sound.setPosition(0);
				sp._doStop(sp.sound.id);
			}
		},

		// id is optional to also enable play...
		toggle: function(id,usestop) {
			var action = usestop ? sp.stop : sp.pause;
			sp.sound = sp.sound ? sp.sound : soundManager.getSoundById(id); // necesito para ie
			if (sp.sound.id !== id) {
				action();
				sp.sound = soundManager.getSoundById(id);
			}
			if (usestop && !sp.sound.paused && sp.sound.playState != 0) {
				sp.stop();
			} else {
				sp.sound.togglePause();
			}
			sp._updateTitle();
		},

		/*
		 * Playlist-specific functions
		 */

		next: function(playlistId,force) {
			//if (!playlistId) {
			//	playlistId = sp.playlist.id;
			//}
			// see above comment-outy stuff. by removing the playlistId part we actually force next
			// and wind up looping through playlists, etc. no bueno...
			sp.loadPlaylist(playlistId);
			var next = false;
			var play = false;
			var pl = sp.playlist;
			if (pl) {
				if (pl.current < pl.tracks.length) {
					next = parseInt(pl.current) + 1;
					play = true;
				} else {
					next = 1;
					if (force) play = true;
				}
				pl.current = next;
			}
			if (play) {
				sp.playlistPlayTrack(pl.id,next);
			}
		},

		previous: function(playlistId) {
			if (!playlistId) {
				playlistId = sp.playlist.id;
			}
			sp.loadPlaylist(playlistId);
			var next = false;
			var pl = sp.playlist;
			if (pl) {
				if (pl.current > 1) {
					next = parseInt(pl.current) - 1;
				} else {
					next = pl.tracks.length;
				}
				pl.current = next;
				sp.playlistPlayTrack(pl.id,next);
			}
		},

		togglePlaylist: function(id) {
			sp.loadPlaylist(id);
			/*
			 * TODO:
			 * this is where we need to check the ajax callback stuff
			 *
			 * if resolve is true, change the url and call the line below
			 * in a callback instead of directly.
			 *
			 */
			sp.toggle(sp.playlist.tracks[sp.playlist.current - 1].id);
		},

		playlistPlayTrack: function(id,track) {
			sp.loadPlaylist(id);
			sp.stop();
			/*
			 * TODO:
			 * this is where we need to check the ajax callback stuff
			 *
			 * if resolve is true, change the url and call the line below
			 * in a callback instead of directly.
			 *
			 */
			sp.playlist.current = track;
			sp.toggle(sp.playlist.tracks[track - 1].id);
		},

		addPlaylist: function(playlist,skipformat) {
			if (!skipformat) {
				playlist = sp._formatPlaylist(playlist);
			}
		 	var pllen = playlist.tracks.length;
			for (var n=0;n<pllen;n++) {
				playlist._index.push(playlist.tracks[n].id);
				soundManager.createSound({
					id: playlist.tracks[n].id,
					url: playlist.tracks[n].url
				});
			}

			sp.playlists[playlist.id] = playlist;
		 },

		loadPlaylist: function(id) {
			if (sp.sound && sp.playlist) {
				if (sp.playlist.id != id) sp.pause();
			}
			if (sp.sound && !sp.playlist) sp.stop();
			sp.playlist = sp.playlists[id];
		},

		drawPlayer: function(container,playlist) {
			if (typeof playlist != 'object') {
				playlist = sp.playlists[playlist]; // if we have a string, not an object, assume id look for it in sp.playlists
			}
			if (typeof playlist == 'object') {
				cm.getTemplate('soundplayer',function(t) {
					t = t.replace(/data-playerid=\"/g,'data-playerid="' + playlist.id);
					t = t.replace(/onPlayer\":\"/g,'onPlayer":"' + playlist.id);
					container.innerHTML = t;
					container.style.visibility = 'visible';
					container.style.display = 'block';
					container.style.position = 'relative';

					var ol = '<ol>';
					var	pllen = playlist.tracks.length;
					for (var n=0;n<pllen;n++) {
						ol += ('<li class="cashmusic soundplayer changetrack" data-track="' + (n+1) + '">' + playlist.tracks[n].title + '</li>');
					}
					ol += '</ol>';

					// add the genrated <ol> from above
					var tl = document.querySelectorAll(
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.tracklist'
					);
					if (tl[0] !== 'undefined') {
						tl[0].innerHTML = ol;
					}

					// pull desired starter content from template, insert it
					var docontent = document.querySelectorAll(
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.nowplaying, ' +
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.playtime, ' +
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.toggletracklist'
					);
					var l = docontent.length;
					for (var li=0;li<l;li++) {
						docontent[li].innerHTML = docontent[li].getAttribute('data-content') + '';
					}

					// add controller events
					var controls = document.querySelectorAll(
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.controls *, ' +
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.toggletracklist, ' +
						'#' + container.id + ' li.cashmusic.soundplayer.changetrack'
					);
					var l = controls.length;
					for (var li=0;li<l;li++) {
						var el = controls[li];
						if (cm.styles.hasClass(el,'playpause')) {
							cm.styles.addClass(el,'paused');
							cm.events.add(el,'click',function(e) {
								sp.togglePlaylist(playlist.id);
							});
						}
						if (cm.styles.hasClass(el,'nexttrack')) {
							cm.events.add(el,'click',function(e) {
								sp.next(playlist.id,true);
							});
						}
						if (cm.styles.hasClass(el,'prevtrack')) {
							cm.events.add(el,'click',function(e) {
								sp.previous(playlist.id);
							});
						}
						if (cm.styles.hasClass(el,'toggletracklist')) {
							cm.events.add(el,'click',function(e) {
								var tracklist = document.querySelectorAll(
									'#' + container.id + ' div.cashmusic.soundplayer.playlist.tracklist'
								);
								var style = tracklist[0].style;
								if (style !== 'undefined') {
									if (style.height !== 'auto') {
										style.height = 'auto';
									} else {
										style.height = '1px';
									}
								}
								el.blur();
							});
						}
						if (cm.styles.hasClass(el,'changetrack')) {
							cm.events.add(el,'click',function(e) {
								var t = (e.currentTarget) ? e.currentTarget : e.srcElement;
								var track = t.getAttribute('data-track');
								sp.playlistPlayTrack(playlist.id,track);
							});
						}
					}

					// add seekbar control
					controls = document.querySelectorAll(
						'#' + container.id + ' div.cashmusic.soundplayer.playlist.seekbar'
					);
					var l = controls.length;
					for (var li=0;li<l;li++) {
						cm.events.add(controls[li],'click',function(e) {
							sp.seek(cm.measure.getClickPosition(e).percentage,playlist.id);
						});
					}
				});
			}
		},



		/***************************************************************************************
		 *
		 * (FAKE) PRIVATE SCOPE FUNCTIONS
		 *
		 ***************************************************************************************/



		/***************************************************************************************
		 *
		 * PSEUDO-EVENT CALLS
		 * All of the querySelectorAll calls seem excessive, but we should respect the idea of
		 * dynamic DOM injection, AJAX, etc. Also these are mostly user-initiated so not often on
		 * a hundreds-per-second scale.
		 *
		 ***************************************************************************************/

		_doFinish: function(detail) {
			sp._switchStylesForCollection(document.querySelectorAll('*.cashmusic.playpause'),'playing','paused');
			sp._updateStyles(sp.styleDivs,'finish');
			sp.next(sp.playlist.id);
			cm.events.fire(cm, "soundplayerFinish", {
				soundId: sp.sound.id
			});
		},

		_doLoad: function(detail) {
			sp._updateStyles(sp.styleDivs,'load');
			cm.events.fire(cm, "soundplayerLoad", {
				soundId: sp.sound.id
			});
		},

		_doLoading: function(detail) {
			sp._updateTweens(sp.tweenDivs,'load',detail.percentage,detail.duration);
			cm.events.fire(cm, "soundplayerLoading", {
				soundId: sp.sound.id,
				percentage: detail.percentage,
				duration: detail.duration
			});
		},

		_doPause: function(detail) {
			// deal with playpause buttons
			sp._switchStylesForCollection(document.querySelectorAll('*.cashmusic.playpause'),'playing','paused');
			sp._updateStyles(sp.styleDivs,'pause');
			cm.events.fire(cm, "soundplayerPause", {
				soundId: sp.sound.id
			});
		},

		_doPlay: function(detail) {
			// we're faking stop with a setposition(0) and pause...so this only fires once
			// routing to doResume instead which fires reliably
			sp._doResume(detail);
			cm.events.fire(cm, "soundplayerPlay", {
				soundId: sp.sound.id
			});
		},

		_doPlaying: function(detail) {
			//console.log('playing: ' + detail.percentage + '% / (' + detail.position + '/' + detail.duration + ')');
			sp._updateTweens(sp.tweenDivs,'play',detail.percentage,detail.duration);
			sp._updateTimes(detail.position);
			// get timecode, fire event if different
			var timecode = sp._getTimecode(detail.position);
			if (timecode != sp.lastTimeEvent) {
				sp.lastTimeEvent = timecode;
				sp._updateStyles(sp.styleDivs,timecode);
			}
			cm.events.fire(cm, "soundplayerPlaying", {
				soundId: sp.sound.id,
				percentage: detail.percentage,
				position: detail.position,
				duration: detail.duration,
				timecode: timecode
			});
		},

		_doResume: function(detail) {
			// update tween/style cache
			sp.styleDivs = document.querySelectorAll('*.cashmusic.setstyles');
			sp.tweenDivs = document.querySelectorAll('*.cashmusic.tween');

			// deal with inline buttons
			var inlineLinks = document.querySelectorAll('a.cashmusic.soundplayer.inline[href="' + sp.sound.id + '"]');
			var l = inlineLinks.length;
			for (var i=0;i<l;i++) {
				cm.styles.swapClasses(inlineLinks[i],'stopped','playing');
			}

			// deal with playpause buttons
			sp._switchStylesForCollection(document.querySelectorAll('*.cashmusic.playpause'),'paused','playing');

			if (sp.sound.position > 0) {
				sp._updateStyles(sp.styleDivs,'play');
			} else {
				sp._updateStyles(sp.styleDivs,'resume');
			}
			cm.events.fire(cm, "soundplayerResume", {
				soundId: sp.sound.id
			});
		},

		_doStop: function(id) {
			// deal with inline buttons
			var inlineLinks = document.querySelectorAll('a.cashmusic.soundplayer.inline[href="' + id + '"]');
			var l = inlineLinks.length;
			for (var i=0;i<l;i++) {
				cm.styles.swapClasses(inlineLinks[i],'playing','stopped');
			}

			sp._updateStyles(sp.styleDivs,'stop');
			cm.events.fire(cm, "soundplayerStop", {
				soundId: sp.sound.id
			});
		},



		/***************************************************************************************
		 *
		 * TWEENS AND STYLE UPDATES
		 *
		 ***************************************************************************************/

		/*
		 * window.cashmusic.soundplayer._checkIds(id,data)
		 * Takes a sound id to match and an object, checks that object for onSound and onPlayer
		 * attributes, and compares to the passed id. If a sound id matches or if the id is part
		 * of the playlist in a playerId it returns true.
		 */
		_checkIds: function(id,data) {
			var soundId = '';
			var playerId = '';
			// get any required sound/player ids
			if (typeof data.onSound !== 'undefined') soundId = data.onSound;
			if (typeof data.onPlayer !== 'undefined') playerId = data.onPlayer;

			if (soundId) {
				if (id.indexOf(soundId) === -1) return false;
			}
			if (playerId) {
				if (!sp._inPlaylist(playerId,id)) return false;
			}

			return true;
		},

		/*
		 * window.cashmusic.soundplayer._getMS(timecode)
		 * Takes an hh:mm:ss (or mm:ss) timecode and returns it as miliseconds
		 */
		_getMS: function(timecode) {
			var timearray = timecode.split(':');
			var miliseconds = 0;
			var l = timearray.length;
			timearray.reverse();
			for (var n=0;n<l;n++) {
				// integer value of the hh/mm/ss chunk, 1/60/3600 as needed, 1000 to go to miliseconds
				miliseconds += parseInt(timearray[n]) * ((n==0) ? 1 : (60 * ((n>1) ? 60 : 1))) * 1000;
			}
			return miliseconds;
		},

		/*
		 * window.cashmusic.soundplayer._getTimecode(miliseconds)
		 * Takes miliseconds and returns hh:mm:ss (or mm:ss or m:ss) timecode
		 */
		_getTimecode: function(miliseconds) {
			var total = Math.floor(miliseconds / 1000);
			var h = Math.floor(total / 3600);
			if (h > 0) {
				// zero-pad if there are hours
				var m = ('00' + (Math.floor((total - (h * 3600)) / 60))).substr(-2);
			} else {
				// no zero-pad if not
				var m = Math.floor(total / 60);
			}
			var s = ('00' + (total - (h * 3600) - (m * 60))).substr(-2);

			if (h > 0) {
				return h + ':' + m + ':' + s;
			} else {
				return m + ':' + s;
			}
		},

		/*
		 * window.cashmusic.soundplayer._checkIds(id,el)
		 * Grabs data attributes and formats them to pass into _checkIds
		 */
		_checkIdsForElement: function(id,el) {
			var data = {};
			data.onSound = el.getAttribute('data-soundid');
			data.onPlayer = el.getAttribute('data-playerid');
			return sp._checkIds(id,data);
		},

		/*
		 * window.cashmusic.soundplayer._updateTweens(elements,type,percentage)
		 * Takes a collection of DOM elements and reads the JSON data stored in
		 * their data-tween attribute, updating the styles based on the passed-in
		 * percentage. A sample JSON object is below.
		 *
		 * Fires on progress for: play, load
		 *
		 * {
		 * 	"play":[
		 * 		{
		 * 			"name":"left",
		 * 			"startAt":0,
		 * 			"endAt":50,
		 * 			"startVal":0,
		 * 			"endVal":250,
		 * 			"units":"px",
		 * 			"onSound":"url",
		 * 			"onPlayer":"playerId"
		 * 		}
		 * 	],
		 * 	"load":[
		 * 		{
		 * 			"name":"left",
		 * 			"startAt":0,
		 * 			"endAt":50,
		 * 			"startVal":0,
		 * 			"endVal":250,
		 * 			"units":"px"
		 * 		}
		 * 	]
		 * }
		 *
		 */
		_updateTweens: function(elements,type,percentage,duration) {
			var eLen = elements.length;
			for (var i=0;i<eLen;i++) {
				var el = elements[i];
				var data = el.getAttribute('data-tween');
				data = JSON.parse(data);
				if (data) {
					if (typeof data[type] !== 'undefined') {
						var dLen = data[type].length;
						var val = false;
						var step = false
						for (var n=0;n<dLen;n++) {
							step = data[type][n];
							if (sp._checkIds(sp.sound.id,step)) {
								// if startAt is timecode get percentage
								if ((step.startAt + '').indexOf(':') !== -1) {
									step.startAt = Math.round((sp._getMS(step.startAt) / duration) * 10000) / 100;
								}
								// if endAt is timecode get percentage
								if ((step.endAt + '').indexOf(':') !== -1) {
									step.endAt = Math.round((sp._getMS(step.endAt) / duration) * 10000) / 100;
								}
								if (percentage >= step.startAt && percentage <= step.endAt) {
									// starting value + ((total value range / total percentage span) * true percentage - startAt percentage)
									val = step.startVal + (((step.endVal - step.startVal) / (step.endAt - step.startAt)) * (percentage - step.startAt));
									if (step.units == 'px') {
										val = Math.floor(val); // round pixels to save CPU
									} else {
										val = val.toFixed(2); // percentage, etc need 2 points for better positioning
									}
									el.style[step.name] = val + step.units;
								}
							}
						}
					}
				}
			}
		},

		/*
		 * window.cashmusic.soundplayer._updateStyles(elements,type)
		 * Updates styles to fixed values for various audio-related events. This
		 * reads the data-styles attribute from a collection of DOM elements,
		 * updating them accordingly. A sample JSON object is below.
		 *
		 * Fires on events for: finish, pause, play, resume, stop, load
		 *
		 * {
		 * 	"stop":[
		 * 		{
		 * 			"name":"left",
		 * 			"val":"250px",
		 * 			"onSound":"url",
		 * 			"onPlayer":"playerId"
		 * 		}
		 * 	]
		 * }
		 */
		_updateStyles: function(elements,type) {
			var eLen = elements.length;
			for (var i=0;i<eLen;i++) {
				var el = elements[i];
				var data = el.getAttribute('data-styles');
				data = JSON.parse(data);
				if (data) {
					if (typeof data[type] !== 'undefined') {
						var dLen = data[type].length;
						for (var n=0;n<dLen;n++) {
							if (sp._checkIds(sp.sound.id,data[type][n])) {
								// check for delay
								if (typeof data[type][n].delay === 'undefined') {
									el.style[data[type][n].name] = data[type][n].val;
								} else {
									// n: name, v: value, d: delay
									(function(el,n,v,d) {
										setTimeout(function() {el.style[n] = v;}, d);
									})(el,data[type][n].name,data[type][n].val,data[type][n].delay);
								}
							}
						}
					}
				}
			}
		},

		/*
		 * window.cashmusic.soundplayer._switchStylesForCollection(collection,oldclass,newclass)
		 * Shortcut — swaps out old styles for new in a given collection
		 */
		_switchStylesForCollection: function(collection,oldclass,newclass) {
			var l = collection.length;
			for (var i=0;i<l;i++) {
				if (sp._checkIdsForElement(sp.sound.id,collection[i]) && !cm.styles.hasClass(sp.sound.id,collection[i],'inline')) {
					cm.styles.swapClasses(collection[i],oldclass,newclass);
				}
			}
		},

		/*
		 * window.cashmusic.soundplayer._updateTimes(position)
		 * Updates all times for playtime elements matching the current sound/playlist.
		 */
		_updateTimes: function(position) {
			var times = document.querySelectorAll('div.cashmusic.soundplayer.playtime');
			var l = times.length;
			for (var n=0;n<l;n++) {
				if (sp._checkIdsForElement(sp.sound.id,times[n])) {
					times[n].innerHTML = sp._getTimecode(position);
				}
			}
		},

		/*
		 * window.cashmusic.soundplayer._updateTitle()
		 * Updates all titles for nowplaying elements matching the current sound/playlist.
		 */
		_updateTitle: function() {
			var titles = document.querySelectorAll('div.cashmusic.soundplayer.nowplaying');
			var l = titles.length;
			for (var n=0;n<l;n++) {
				if (sp._checkIdsForElement(sp.sound.id,titles[n])) {
					titles[n].innerHTML = sp.playlist.tracks[sp.playlist.current - 1].title;
				}
			}
		},





		/*
		 * window.cashmusic.soundplayer._formatPlaylist(playlist,useid,uniqueseed)
		 * Takes a playlist and formats it, ensuring all required attributes are
		 * set and assigns a unique id if none has been defined.
		 *
		 * Example playlist:
		 * {
		 * 	id: string,
		 * 	current: int (tracknumber) / null,
		 * 	artist: string / null,
		 * 	album: string / null,
		 * 	cover: url / null,
		 * 	url: url / null,
		 * 	options: null,
		 * 	tracks: [
		 * 		{
		 * 			id: string,
		 * 			url: url,
		 * 			title: string,
		 * 			artist: string,
		 * 			ISRC: string,
		 * 			album: string,
		 * 			label: string,
		 * 			cover: url,
		 * 			link: url,
		 * 			resolve: bool
		 * 		}
		 * 	]
		 * }
		 *
		 */
		_formatPlaylist: function(playlist,useid,uniqueseed) {
			playlist = playlist ? playlist : {};
			if (!playlist.id) {
				playlist.id = useid ? useid : 'pl---' + uniqueseed; // the 'pl---' is unusual on purpose
			}
			playlist.current = 1;//int (tracknumber) / null
			playlist.tracks = playlist.tracks ? playlist.tracks : [];// []
			playlist._index = [];

			return playlist;
		},

		/*
		 * window.cashmusic.soundplayer._formatTrack(a,playlist)
		 * Formats a track pulled from an anchor to ensure all attributes are set.
		 */
		_formatTrack: function(a,playlist) {
			var track = JSON.parse(a.getAttribute('data-track'));
			track = track ? track : {};
			track.url = track.url ? track.url : a.href;
			track.title = track.title ? track.title : (a.innerText || a.textContent);
			track.id = playlist + track.url;

			return track;
		},

		/*
		 * window.cashmusic.soundplayer._inPlaylist(playlistid,soundid)
		 * Tests if a current sound id is present in a given playlist. Matches using indexof so
		 * any playlist id appended in front of a known/set id won't break the match. This makes
		 * it a slightly fuzzy match, but provides more upside than down.
		 */
		_inPlaylist: function(playlistid,soundid) {
			if (playlistid) {
				return (sp.playlists[playlistid]._index.indexOf(soundid) > -1) ? true : false;
			} else {
				return false;
			}
		}

	};



	window.SM2_DEFER = true; // force SM2 to defer auto-init, allow us to change defaults, etc.
	cm.loadScript(cm.path+'/lib/soundmanager/soundmanager2.js', function() {
		sp = cm.soundplayer;
		window.soundManager = new SoundManager();

		/***************************************************************************************
		 *
		 * SM2 SETUP AND INITIALIZATION
		 *
		 ***************************************************************************************/

		soundManager.setup({
			debugMode: false,
			debugFlash: false,
			preferFlash: false,
			allowScriptAccess: 'always',
			url: cm.path+'/lib/soundmanager/swf/',
			flashVersion: 8,
			flashLoadTimeout: 7500,
			flashPollingInterval:30,
			html5PollingInterval:30,
			useHighPerformance:true,
			onready: function() {
				sp._init();
			},
			// ontimeout: function(status) {
			// 	console.log('SM2 failed to start. Flash missing, blocked or security error?');
			// 	console.log('Trying: ' + soundManager.url);
			// },
			defaultOptions: {
				onload: function() {
				 	sp._doLoad({id: this.id});
				},
				onstop: function() {
					sp._doStop({id: this.id});
				},
				onfinish: function() {
					sp._doFinish({id: this.id});
				},
				onpause: function() {
					sp._doPause({id: this.id});
				},
				onplay: function() {
					sp._doPlay({id: this.id});
				},
				onresume: function() {
					sp._doResume({id: this.id});
				},
				stream: true,
				usePolicyFile: false,
				volume: 100,
				whileloading: function() {
					sp._doLoading({
						id: this.id,
						loaded: this.bytesLoaded,
						total: this.bytesTotal,
						percentage: Math.round((this.bytesLoaded / this.bytesTotal) * 1000) / 10
					});
				},
				whileplaying: function() {
					var p = Math.round((this.position / this.duration) * 10000) / 100;
					sp._doPlaying({
						id: this.id,
						position: this.position,
						duration: this.duration,
						percentage: p
					});
				}
			}
		});
		// Deals with SM2 initialization. By default, SM2 does all this automatically if not deferred.
		soundManager.beginDelayedInit();
	});
}()); // ha. closures look silly.
