;

// Page draw / AJAX stuff
function collapseAllTabs(section) {
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

	setContentBehaviors();
}

window.addEventListener("popstate", function(e) {
	refreshPageData(location.pathname,null,null,null,true);
});

function setContentBehaviors() {
	jQuery('.needsconfirmation').on('click', function(e) {
		e.preventDefault();
		doModalConfirm( jQuery(this).attr('href'));
	});
	
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

	jQuery('a.injectbefore').on('click', function(e) {
		// grabs rel, inserts rev data and iterates the name, changing the rel
		// should probably move to a data- structure
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

function doModalConfirm(url) {

	var markup = '<div class="modalbg"><div class="modaldialog">' +
				 '<h2>Are You Sure?</h2><br /><div class="tar">' +
				 '<input type="button" class="button modalcancel" value="Cancel" />' +
				 '<input type="button" class="button modalyes" value="Yes do it" />' + 
				 '</div></div></div>';
	markup = jQuery(markup);
	markup.hide();
	jQuery('body').append(markup);

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

	jQuery('.modalbg').fadeIn('fast');
}

jQuery(document).ready(function() {
	prepAJAX();
	setUIBehaviors();
});
