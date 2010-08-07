window.addEvent('domready', function() {
    $$('div.navitem').each(function(item){
    	item.addEvent('click', function(e) {
    		window.location.pathname = item.getElement('a').getProperty('href');
    	});
	});
});