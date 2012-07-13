/**
 * JavaScript behaviors for the CASH admin
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the BSD license.
 * See http://www.opensource.org/licenses/bsd-license.php/
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
			} else {
				if (showerror) {
					data.error_message = showerror;
				}
				if (showmessage) {
					data.page_message = showmessage;
				}
				redrawPage(data);
			}
		}
	},'json');
}

// changes all link behavior to work via AJAX loads as well as form behaviors
// runs every page load and sets up events for new page load
function prepAJAX() {
	// cashAdminPath is set in the main template to the www_base of the admin
	jQuery(document).on('click', 'a[href^=' + cashAdminPath + ']', function(event) {
		if (!event.altKey && !event.ctrlKey && !event.metaKey && !event.shiftKey && !jQuery(event.currentTarget).hasClass('navitemlink') && !jQuery(event.currentTarget).hasClass('needsconfirmation')) {
			event.preventDefault();
			var url = jQuery(event.currentTarget).attr('href');
			refreshPageData(url);
			event.currentTarget.blur();
		}
	});
	
	jQuery('form').on('submit', function(event) {
		event.preventDefault();
		var url = jQuery(this).attr('action');
		if (url == '') {
			url = location.pathname;
		}
		var formdata = jQuery(this).serialize();
		refreshPageData(url,formdata);
	});
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
		e.preventDefault();
		refreshPageData(jQuery(this).find('a').attr('href'));
	});

	jQuery('.navitemlink').on('click', function(e) {
		e.preventDefault();
		this.blur();
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
	jQuery('.modalcancel').on('click', function(e) {
		e.preventDefault();
		jQuery('.modalbg').fadeOut('fast', function() {
			jQuery('.modalbg').remove();
		});
	});
	jQuery('.modalyes').on('click', function(e) {
		e.preventDefault();
		refreshPageData(url,'modalconfirm=1&redirectto='+location.pathname.replace(cashAdminPath, ''));
		jQuery('.modalbg').remove();
	});

	// show the dialog with a fast fade-in
	jQuery('.modalbg').fadeIn('fast');
}

jQuery(document).ready(function() {
	prepAJAX();
	setUIBehaviors();
});
