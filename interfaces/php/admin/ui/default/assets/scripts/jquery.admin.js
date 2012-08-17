/**
 * JavaScript behaviors for the CASH admin
 *
 * @package diy.org.cashmusic
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

/**
 * AJAX specific functions
 **/

// handle per-element specific redraws for each request
function redrawPage(data) {
	// change the color
	jQuery('#mainspc').removeClass();
	jQuery('#mainspc').addClass(data.specialcolor);

	// tabs
	collapseAllTabs(data.section_name);

	// the rest
	jQuery('#pagemessage').html('');
	if (data.error_message) {
		jQuery('#pagemessage').html('<p><span class="highlightcopy errormessage">'+data.error_message+'</span></p>');
	}
	if (data.page_message) {
		jQuery('#pagemessage').html('<p><span class="highlightcopy">'+data.page_message+'</span></p>');
	}
	jQuery('#pagetips').hide();
	jQuery('#current_pagetip').html(data.ui_page_tip);
	jQuery('#pagedisplay').html(data.content);
	jQuery('#pagetitle').html(data.ui_title);
	jQuery('#pagemenu').html(data.section_menu);

	window.scrollTo(0,0);
}

// handles the data request for each page load, manipulates history,
// and decides redraw method (full redraw or redrawPage)
function refreshPageData(url,formdata,showerror,showmessage,skiphistory) {
	if (!formdata) {
		formdata = '';
	} else {
		formdata = formdata+'&';
	}
	// fade out
	jQuery('#pagedisplay').fadeTo(100,0.2, function() {
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
					jQuery('body').html(newbody);
					setUIBehaviors();
					prepAJAX();
				} else {
					if (showerror) {
						data.error_message = showerror;
					}
					if (showmessage) {
						data.page_message = showmessage;
					}
					redrawPage(data);
					jQuery(document).off("click", "a[href^=" + cashAdminPath + "]");
					prepAJAX();
				}
			}
			jQuery('#pagedisplay').fadeTo(200,1);
		},'json');
	});
}

// changes all link behavior to work via AJAX loads as well as form behaviors
// runs every page load and sets up events for new page load
function prepAJAX() {
	// cashAdminPath is set in the main template to the www_base of the admin
	jQuery(document).on('click', 'a[href^=' + cashAdminPath + ']', function(event) {
		var el = jQuery(event.currentTarget);
		if (!event.altKey && !event.ctrlKey && !event.metaKey && !event.shiftKey && !el.hasClass('navitemlink')  && !el.hasClass('lightboxed') && !el.hasClass('needsconfirmation')) {
			event.preventDefault();
			var url = jQuery(event.currentTarget).attr('href');
			refreshPageData(url);
			event.currentTarget.blur();
		}
	});

	jQuery('form').bind('submit', function(event) {
		var el = jQuery(event.currentTarget);
		if (!el.is('.modallightbox form')) {	
			event.preventDefault();
			var url = jQuery(this).attr('action');
			if (url == '') {
				url = location.pathname;
			}
			var formdata = jQuery(this).serialize();
			refreshPageData(url,formdata);
		}
	});

	setContentBehaviors();
}

// make the back button work
// also make the forward button work
window.addEventListener("popstate", function(e) {
	refreshPageData(location.pathname,null,null,null,true);
});

/**
 * UI element behaviors
 **/

// collapse all main nav tabs, opening one if a section is specified
function collapseAllTabs(section) {
	//
	if (section != currentSection) {
		currentSection = section;
		jQuery('#navmenu div').each(function(index) {
			jQuery(this).removeClass('currentnav');
			if (jQuery(this).attr('id') == section+'tab') {
				jQuery(this).addClass('currentnav');
			}
		});
	}
}

// miscellaneous behaviors for various things — needs loading each page load
function setContentBehaviors() {
	// modal pop-ups
	jQuery('.needsconfirmation').on('click', function(e) {
		e.preventDefault();
		doModalConfirm( jQuery(this).attr('href'));
		this.blur();
	});

	// modal lightboxes
	jQuery('.lightboxed').on('click', function(e) {
		e.preventDefault();
		doModalLightbox( jQuery(this).attr('href'));
		this.blur();
	});

	// show/hide element details
	jQuery('.showelementdetails').on('click', function(e) {
		e.preventDefault();
		jQuery(this).html( function(e) {
			var t = jQuery(this).html(),
			isShown = jQuery(this).parents('.itemnav').prev('.elementdetails').hasClass('detailsshown');

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
	jQuery('a.injectbefore').on('click', function(e) {
		e.preventDefault();
		var iteration = jQuery(e.currentTarget).attr('rel');
		if (iteration) {
			jQuery.data(e.currentTarget,'nameiteration',iteration);
		} else {
			iteration = 1;
		}
		jQuery(e.currentTarget).attr('rel',iteration);
		var toinsert = jQuery(e.currentTarget).attr('rev');
		var names = toinsert.match(/name='([^']*)/g);
		if (names) {
			jQuery.each(names, function(index, name) {
				toinsert = toinsert.replace(name, name+iteration);
			});
		}
		jQuery(e.currentTarget).before('<div>' + toinsert + '</div>');
		jQuery(e.currentTarget).attr('rel',iteration+1);
	});

	// high-level datepicker
	jQuery('input[type=date]').datepicker();

	// high-level autocomplete
	// would be nice to find a way to do this without a global
	var acURL;
	jQuery('.autocomplete').each( function() {
		acURL = $(this).data('cash-endpoint-url');
		//console.log(acURL);
	}).autocomplete({
		// probably should do some error handling here.
		source: function( request, response ) {
			//console.log('request: ', request);
			//console.log('response: ', response);
			//console.log('url: ', acURL);

			// it seems likely that I'll need to pass request.term somewhere in here.
			$.ajax({
				url: acURL,
				dataType: "json",
				error: function( data) {
					//console.log('url: ', acURL);
					//console.log('error: ', data);
				},
				success: function( data ) {
					//console.log('data: ', data);

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
		select: function( event, ui) {
			//console.log('changed, new item is: ', ui.item);

			// this is pretty ugly
			$('#event_venue').val( ui.item.id );
		},
		minLength: 2
	});

	prepDrawers('<span class="icon arrow_up"></span> Hide','<span class="icon arrow_down"></span> Show');
}

// the main UI behaviors — only needs to be run on the first page load,
// not on each AJAX load-in
function setUIBehaviors() {
	jQuery('#pagetips').hide();

	jQuery('#tipslink').on('click', function(e) {
		e.preventDefault();
		jQuery('#pagetips').slideDown(200);
	});

	jQuery('#tipscloselink').on('click', function(e) {
		e.preventDefault();
		jQuery('#pagetips').slideUp(100);
	});

	jQuery('.navitem').on('click', function(e) {
		if (!e.altKey && !e.ctrlKey && !e.metaKey && !e.shiftKey) {	
			e.preventDefault();
			refreshPageData(jQuery(this).find('a').attr('href'));
		}
	});

	jQuery('.navitemlink').on('click', function(e) {
		if (!e.altKey && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
			e.preventDefault();
		}
		this.blur();
	});

	// overlay cancel button event
	jQuery(document).on('click', '.modalcancel', function(e) {
		e.preventDefault();
		jQuery('.modalbg').fadeOut('fast', function() {
			jQuery('.modalbg').remove();
		});
	});

	jQuery(document).keyup(function(e) {
		if(e.keyCode === 27) {
			jQuery('.modalbg').fadeOut('fast', function() {
				jQuery('.modalbg').remove();
			});
		}
	});
}

// opens a modal confirmation box for delete links, etc
function doModalConfirm(url) {
	// markup for the confirmation link
	var markup = '<div class="modalbg"><div class="modaldialog">' +
				 '<h2>Are You Sure?</h2><br /><div class="tar">' +
				 '<input type="button" class="button modalcancel" value="Cancel" />' +
				 '<input type="button" class="button modalyes" value="Yes do it" />' +
				 '</div></div></div>';
	markup = jQuery(markup);
	markup.hide();
	jQuery('body').append(markup);

	// button events
	jQuery('.modalyes').on('click', function(e) {
		e.preventDefault();
		refreshPageData(url,'modalconfirm=1&redirectto='+location.pathname.replace(cashAdminPath, ''));
		jQuery('.modalbg').remove();
	});

	// show the dialog with a fast fade-in
	jQuery('.modalbg').fadeIn('fast');
}

// opens a modal input form from a specific route
function doModalLightbox(route) {
	jQuery.post(route,'data_only=1', function(data) {
		// markup for the confirmation link
		var markup = '<div class="modalbg"><div class="modallightbox ' + data.specialcolor + '">' +
					 data.page_content_markup +
					 '<div class="tar"><a href="#" class="modalcancel">cancel</a></div>' +
					 '</div></div></div>';
		markup = jQuery(markup);
		markup.hide();
		jQuery('body').append(markup);

		// show the dialog with a fast fade-in
		jQuery('.modalbg').fadeIn('fast');
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

	jQuery('.drawer').each(function() {
		// minimize jQuery calls and simplify. set each element up fron in the function scope:
		var drawer, drawerHandle, drawerContent, drawerHandleLabel;
		drawer = jQuery(this);
		drawerHandle = drawer.find('.drawerhandle');
		drawerContent = drawer.find('.drawercontent');
		// create the label span and add necessary classes
		drawerHandleLabel = jQuery('<span class="drawerhandleaction">' + labelTextHidden + ' </span>');
		if (labelClassVisible) {
			drawerHandleLabel.addClass(labelClassHidden);
		}
		// first hide the content add a label to all the drawerhandles
		drawerContent.hide();
		drawerHandle.prepend(drawerHandleLabel);
		// then set up click actions on each of them
		jQuery(this).find('.drawerhandle').on('click',function () {
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
	});

}

jQuery(document).ready(function() {
	prepAJAX();
	setUIBehaviors();


});
