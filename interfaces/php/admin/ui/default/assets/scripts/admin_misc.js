window.addEvent('domready', function() {
	$$('div.navitem').each(function(item){
		item.addEvent('click', function(e) {
			window.location = item.getElement('a').getProperty('href');
		});
	});
	
	document.id('pagetips').fade('hide');
	document.id('tipslink').addEvent('click', function(e) {
		e.stop();
		document.id('pagetips').fade('in');
	});
	document.id('tipscloselink').addEvent('click', function(e) {
		e.stop();
		document.id('pagetips').fade('out');
	});
});