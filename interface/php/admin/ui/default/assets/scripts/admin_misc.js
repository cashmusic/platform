window.addEvent('domready', function() {
    $$('div.navitem').each(function(item){
    	item.addEvent('click', function(e) {
    		window.location = item.getElement('a').getProperty('href');
    	});
	});
});