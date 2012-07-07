;

// Page draw / AJAX stuff
function collapseAllTabs(section) {
	if (section != currentSection) {
		currentSection = section;
		jQuery('#navmenu div').each(function(index) {
			this.removeClass('currentnav');
			if (jQuery(this).attr('id') == section+'tab') {
				this.addClass('currentnav');
			}
		});
	}
}

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
}

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
				history.pushState(url, null, url);
			}
			if (data.fullredraw) {
				var doc=document.open("text/html");
				doc.write(data.fullcontent);
				doc.close();
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
	},'json');
}

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
	
	jQuery('form').bind('submit', function(event) {
		event.preventDefault();
		var url = jQuery(this).attr('action');
		if (url == '') {
			url = location.pathname;
		}
		var formdata = jQuery(this).serialize();
		refreshPageData(url,formdata);
	});
}

window.onpopstate = function(event){
	refreshPageData(location.pathname,null,null,null,true);
}

$(document).ready(function() {

	// set initial state

	prepAJAX();
	//alert('load');

	/* begin mootools port */
	
	$('#pagetips').hide();
	
	$('#tipslink').on('click', function(e) {
		e.preventDefault();
		$('#pagetips').slideDown(200);
	});
	
	$('#tipscloselink').on('click', function(e) {
		e.preventDefault();
		$('#pagetips').slideUp(100);
	});
	
	$('.navitem').on('click', function(e) {
		e.preventDefault();
		refreshPageData(jQuery(this).find('a').attr('href'));
	});

	$('.navitemlink').on('click', function(e) {
		e.preventDefault();
		this.blur();
	});
	
	$('.needsconfirmation').on('click', function(e) {
		e.preventDefault();
		doModalConfirm( $(this).attr('href'));
	});
	
	$('.showelementdetails').on('click', function(e) {
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
	/* end mootools port */
});
