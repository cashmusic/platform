/**
 * JavaScript behaviors for the CASH admin
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
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
;

(function($) {

	// initial load setup:
	$(document).ready(function() {
		var currentPath = location.pathname;

		setUIBehaviors();
		setContentBehaviors();

		// make back/forward buttons work
		window.addEventListener("popstate", function(e) {
			if (location.pathname != currentPath) {
				refreshPageData(location.pathname,null,null,null,true);
				currentPath = location.pathname;
			}
		});
	});

	/**
	 *
	 *
	 *
	 * Page redraw and AJAX requests
	 *
	 *
	 *
	 **/

	/**
	 * redrawPage (function)
	 *
	 * handle per-element specific redraws for each request
	 *
	 */
	function redrawPage(data) {
		// change the color
		$('#mainspc').removeClass();
		$('#mainspc').addClass(data.specialcolor);

		// tabs
		collapseAllTabs(data.section_name);

		// the rest
		$('#pagemessage').html('');
		if (data.error_message) {
			$('#pagemessage').html('<p><span class="highlightcopy errormessage">'+data.error_message+'</span></p>');
		}
		if (data.page_message) {
			$('#pagemessage').html('<p><span class="highlightcopy">'+data.page_message+'</span></p>');
		}
		$('#pagetips').hide();
		$('#current_pagetip').html(data.ui_page_tip);
		$('#pagedisplay').html(data.content);
		$('#pagetitle').html(data.ui_title);
		$('#pagemenu').html(data.section_menu);

		window.scrollTo(0,0);
	}

	/**
	 * refreshPageData (function)
	 *
	 * handles the data request for each page load, manipulates history,
	 * and decides redraw method (full redraw or redrawPage)
	 *
	 */
	function refreshPageData(url,formdata,showerror,showmessage,skiphistory) {
		if (!formdata) {
			formdata = '';
		} else {
			formdata = formdata+'&';
		}
		// remove any dialogs
		$('.modalbg').fadeOut('fast', function() {
			$('.modalbg').remove();
		});

		// fade out
		$('#pagedisplay').fadeTo(100,0.2, function() {
			// do a POST to get the page data, change pushstate, redraw page
			jQuery.post(url, formdata+'data_only=1', function(data) {
				if (data.doredirect) {
					if (data.showerror) {
						refreshPageData(data.location,false,data.showerror);
					} else if (data.showmessage) {
						refreshPageData(data.location,false,false,data.showmessage);
					} else {
						refreshPageData(data.location);
					}
				} else {
					if (!skiphistory) {
						history.pushState(null, null, url);
					}
					if (data.fullredraw) {
						var newbody = data.fullcontent.replace(/^[\s\S]*?<body[^>]*>([\s\S]*?)<\/body>[\s\S]*?$/i,"$1");
						$('body').html(newbody);
					} else {
						if (showerror) {
							data.error_message = showerror;
						}
						if (showmessage) {
							data.page_message = showmessage;
						}
						redrawPage(data);
					}
					setContentBehaviors();
				}
				$('#pagedisplay').fadeTo(200,1);
			},'json');
		});
	}

	/**
	 *
	 *
	 *
	 * UI element behaviors
	 *
	 *
	 *
	 **/

	/**
	 * collapseAllTabs (function)
	 *
	 * collapse all main nav tabs, opening one if a section is specified
	 *
	 */
	function collapseAllTabs(section) {
		//
		if (section != currentSection) {
			currentSection = section;
			$('#navmenu div').each(function(index) {
				$(this).removeClass('currentnav');
				if ($(this).attr('id') == section+'tab') {
					$(this).addClass('currentnav');
				}
			});
		}
	}

	/**
	 * setContentBehaviors (function)
	 *
	 * miscellaneous behaviors for various things — needs to run each AJAX page load
	 *
	 */
	function setContentBehaviors() {
		// show/hide drawers
		prepDrawers('<span class="icon arrow_up"></span> Hide','<span class="icon arrow_down"></span> Show');

		// datepicker
		$('input[type=date],input.date').datepicker();

		// autocomplete
		$('.autocomplete').each( function() {
			var acURL = $(this).data('cash-endpoint-url');
			$(this).autocomplete({
				// probably should do some error handling here.
				source: function( request, response ) {
					$.ajax({
						url: acURL + '/' + request.term,
						dataType: "json",
						error: function( data) {},
						success: function( data ) {
							response( $.map( data, function( item ) {
								return {
									label: item.displayString,
									value: item.displayString,
									id: item.id
								}
							}));
						}
					})
				},
				select: function( event, ui ) {
					// TODO: this is pretty ugly
					$('#event_venue').val( ui.item.id );
				},
				minLength: 2
			});
		});

		$('#connection_id').each( function() {

			if ( this.value > 0 ) {
				//var connectionID = this.value;
				var newUploadEndpoint = $('.file-upload-trigger').data('upload-endpoint') + this.value;

				$('.upload-corral').fadeIn().find('.file-upload-trigger').data('upload-endpoint', newUploadEndpoint );
			}
		});

	}

	/**
	 * setUIBehaviors (function)
	 *
	 * The main UI behaviors — only needs to be run on the first page load, not on
	 * each AJAX load-in, bind all events with on to document to preserve cross-load
	 *
	 */
	function setUIBehaviors() {
		$('#pagetips').hide();

		$(document).on('click', '#tipslink', function(e) {
			e.preventDefault();
			$('#pagetips').slideDown(200);
		});

		$(document).on('click', '#tipscloselink', function(e) {
			e.preventDefault();
			$('#pagetips').slideUp(100);
		});

		$(document).on('click', '.navitem', function(e) {
			if (!e.altKey && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
				e.preventDefault();
				refreshPageData($(this).find('a').attr('href'));
			}
		});

		$(document).on('click', '.navitemlink', function(e) {
			if (!e.altKey && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
				e.preventDefault();
			}
			this.blur();
		});

		$(document).on('click', '#logout', function(e) {
			e.preventDefault();
			jQuery.post(cashAdminPath+'/logout','noredirect=1');
			refreshPageData(cashAdminPath+'/');
		});

		$(document).on('click', 'input.externalsubmit', function(e) {
			$($(this).data('cash-target-form')).submit();
		});

		// overlay cancel button event
		$(document).on('click', '.modalcancel', function(e) {
			e.preventDefault();
			$('.modalbg').fadeOut('fast', function() {
				$('.modalbg').remove();
			});
		});

		$(document).keyup(function(e) {
			if(e.keyCode === 27) {
				$('.modalbg').fadeOut('fast', function() {
					$('.modalbg').remove();
				});
			}
		});

		// to-be-copied code
		$(document).on('click', 'code input', function(e) {
			$(this).select();
		});

		// modal pop-ups
		$(document).on('click', '.needsconfirmation', function(e) {
			e.preventDefault();
			doModalConfirm( $(this).attr('href'));
			this.blur();
		});

		// modal lightboxes
		$(document).on('click', '.lightboxed', function(e) {
			e.preventDefault();
			if ($(this).hasClass('returntocurrentroute')) {
				doModalLightbox($(this).attr('href'),true);
			} else {
				doModalLightbox($(this).attr('href'));
			}
			this.blur();
		});

		// show/hide element details
		$(document).on('click', '.showelementdetails', function(e) {
			e.preventDefault();
			$(this).html( function(e) {
				var t = $(this).html(),
				isShown = $(this).parents('.itemnav').prev('.elementdetails').hasClass('detailsshown');
				if ( isShown ) {
					t = t.replace(/Less/g, 'More');
				} else {
					t = t.replace(/More/g, 'Less');
				}
				return t;
			}).parents('.itemnav').prev('.elementdetails').toggleClass('detailsshown');
		});

		// inserts html into the current document/form (dynamic inputs primarily)
		// grabs rel, inserts rev data and iterates the name, changing the rel
		// should probably move to a data- structure
		$(document).on('click', 'a.injectbefore', function(e) {
			e.preventDefault();
			e.currentTarget.blur();
			var iteration = $(e.currentTarget).attr('rel');
			if (iteration) {
				jQuery.data(e.currentTarget,'nameiteration',iteration);
			} else {
				iteration = 1;
			}
			$(e.currentTarget).attr('rel',iteration);
			var toinsert = $(e.currentTarget).attr('rev');
			var names = toinsert.match(/name='([^']*)/g);
			if (names) {
				jQuery.each(names, function(index, name) {
					toinsert = toinsert.replace(name, name+iteration);
				});
			}
			$(e.currentTarget).before('<div>' + toinsert + '</div>');
			$(e.currentTarget).attr('rel',iteration+1);
		});

		// open local (admin) links via AJAX
		// cashAdminPath is set in the main template to the www_base of the admin
		$(document).on('click', 'a[href^="' + cashAdminPath + '"]', function(e) {
			var el = $(e.currentTarget);
			if (!e.altKey && !e.ctrlKey && !e.metaKey && !e.shiftKey && !el.hasClass('navitemlink')
				&& !el.hasClass('lightboxed') && !el.hasClass('needsconfirmation') && !el.hasClass('showelementdetails')
				 && !el.hasClass('noajax') && !el.is('#logout')
			) {
				e.preventDefault();
				var url = $(e.currentTarget).attr('href');
				refreshPageData(url);
				el.blur();
			}
		});

		// submit forms via AJAX
		$(document).on('submit', 'form', function(e) {
			var el = $(e.currentTarget);
			if (el.attr('action').toLowerCase().indexOf('s3.amazonaws') < 1) {
				e.preventDefault();
				var url = el.attr('action');
				if (url == '') {
					url = location.pathname;
				}
				var formdata = $(this).serialize();
				if (el.is('.returntocurrentroute form')) {
					formdata += '&forceroute=' + location.pathname.replace(cashAdminPath, '');
				}
				refreshPageData(url,formdata);
			}
		});

		// publicize
		$(document).on('click', 'a[data-publicize-endpoint]', function(e) {
			e.preventDefault();

			var publicize = $.ajax({
				url: $(this).data('publicize-endpoint'),
				dataType: 'json'
			}).done(function(result) {
				}).complete(function(result) {
					var response = $.parseJSON(result.responseText);
					if (response.success) {
						$('#asset_location').val(response.location);
						$('#connection_id').val('0');
						$('.upload-corral').fadeOut();
					}
				});

		});

		// storage connection change handler
		$(document).on('change', '#connection_id', function(e) {
			if ( this.value > 0 ) {
				//var connectionID = this.value;
				var newUploadEndpoint = $('.file-upload-trigger').data('upload-endpoint') + this.value;

				var trigger = $('.upload-corral').fadeIn().find('.file-upload-trigger')
				trigger.data('upload-endpoint', newUploadEndpoint );

				var uploadTo = $.ajax({
					url: newUploadEndpoint,
					dataType: 'json',
					data: 'data_only=1'
				}).done(function(result) {
					//trigger.parents('.fadedtext').fadeOut( function() {
						trigger.parents('.drawer').find('.drawercontent').html(result.content);
					//});
				});
			} else {
				$('.upload-corral').fadeOut();
			}
		});

		// file upload handlers
		$(document).on('click', '.file-upload-trigger', function(e) {
			e.preventDefault();

			var trigger = $(this),
			iframeSrc = $(this).data('upload-endpoint'),
			connectionID = $('#connection_id').val();

			//console.log('is this iframeSrc? ', iframeSrc);

			if ( connectionID == '0' ) {
				alert('Sorry, can\'t upload without a connection. Have you tried a normal link?');
				return false;
			} else {
				trigger.parents('.fadedtext').animate({ opacity: 0 });
			}
		});
	}

	/**
	 *
	 *
	 *
	 * Dialogs, lightboxes, and other content display enhancements
	 *
	 *
	 *
	 **/

	/**
	 * doModalLightbox (function)
	 *
	 * opens a modal confirmation box for delete links, etc. essentially this is a
	 * silly "are you sure you want to click this?" message, and it sends along a
	 * GET param saying that it's been clicked — so the receiving controller knows
	 * it's happened and can skip displaying any form confirmation, etc.
	 *
	 */
	function doModalConfirm(url) {
		// markup for the confirmation link
		var markup = '<div class="modalbg"><div class="modaldialog">' +
					 '<h2>Are You Sure?</h2><br /><div class="tar">' +
					 '<input type="button" class="button modalcancel" value="Cancel" />' +
					 '<input type="button" class="button modalyes" value="Yes do it" />' +
					 '</div></div></div>';
		markup = $(markup);
		markup.hide();
		$('body').append(markup);

		// button events
		$('.modalyes').on('click', function(e) {
			e.preventDefault();
			refreshPageData(url,'modalconfirm=1&redirectto='+location.pathname.replace(cashAdminPath, ''));
			$('.modalbg').remove();
		});

		// show the dialog with a fast fade-in
		$('.modalbg').fadeIn('fast');
	}

	/**
	 * doModalLightbox (function)
	 *
	 * opens a modal input form from a specific route
	 *
	 */
	function doModalLightbox(route,returntocurrentroute) {
		jQuery.post(route,'data_only=1', function(data) {
			var addedClass = '';
			if (returntocurrentroute) {
				addedClass = 'returntocurrentroute '
			}
			// markup for the confirmation link
			var markup = '<div class="modalbg"><div class="modallightbox ' + addedClass +
						 data.specialcolor + '">' +
						 data.content + //jQuery.param(data) +
						 '<div class="tar" style="position:relative;z-index:9876;"><a href="#" class="modalcancel smalltext"><span class="icon denied"></span> cancel</a></div>' +
						 '</div></div></div>';

			markup = $(markup);
			markup.hide();
			$('body').append(markup);
			prepDrawers('<span class="icon arrow_up"></span> Hide','<span class="icon arrow_down"></span> Show');

			// show the dialog with a fast fade-in
			$('.modalbg').fadeIn('fast');
		},'json');
	}

	/**
	 * prepDrawers (function)
	 *
	 * Simple function to roll-up and roll-down content inside a div with class "drawer" â€” will
	 * look for a "handle" inside the div â€” an element that triggers the effect on click and remains
	 * visible throughout.
	 *
	 * Pass labelTextVisible/labelTextHidden to prepend the handle width "show"/"hide" type text
	 * Pass labelClassVisible/labelClassHidden to add classes for visible/hidden states
	 *
	 * Automatically closes all drawers and attaches event handlers
	 *
	 */
	function prepDrawers(labelTextVisible,labelTextHidden,labelClassVisible,labelClassHidden) {

		$('.drawer').each(function() {
			// minimize jQuery calls and simplify. set each element up fron in the function scope:
			var drawer, drawerHandle, drawerContent, drawerHandleLabel;
			drawer = $(this);
			if (drawer.find('.drawerhandleaction').length == 0) {
				if (drawer.hasClass('noprefix')) {
					labelTextHidden = '';
					labelTextVisible = '';
				}
				drawerHandle = drawer.find('.drawerhandle');
				drawerContent = drawer.find('.drawercontent');
				// create the label span and add necessary classes
				drawerHandleLabel = $('<span class="drawerhandleaction">' + labelTextHidden + ' </span>');
				if (labelClassVisible) {
					drawerHandleLabel.addClass(labelClassHidden);
				}
				// first hide the content add a label to all the drawerhandles
				drawerContent.hide();
				drawerHandle.prepend(drawerHandleLabel);
				// then set up click actions on each of them
				$(this).find('.drawerhandle').on('click',function () {
					$(this).blur();
					if (drawerContent.is(':hidden')) {
						drawerContent.slideDown(200, function () {
							drawerHandleLabel.html(labelTextVisible + ' ');
							if (labelClassVisible) {
								drawerHandleLabel.removeClass();
								drawerHandleLabel.addClass(labelClassVisible);
							}
						});
					} else {
						drawerContent.slideUp(200, function () {
							drawerContent.hide();
							drawerHandleLabel.html(labelTextHidden + ' ');
							if (labelClassHidden) {
								drawerHandleLabel.removeClass();
								drawerHandleLabel.addClass(labelClassHidden);
							}
						});
					}
				});
			}
		});

	}
})(jQuery);