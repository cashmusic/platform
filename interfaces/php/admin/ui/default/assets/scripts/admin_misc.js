window.addEvent('domready', function() {
	$$('div.navitem').each(function(item){
		item.addEvent('click', function(e) {
			window.location = item.getElement('a').getProperty('href');
		});
	});
	$$('a.needsconfirmation').each(function(item){
		item.addEvent('click', function(e) {
			e.stop();
			doModalConfirm(item.getProperty('href'));
		});
	});
	$$('a.injectbefore').each(function(item){
		item.addEvent('click', function(e) {
			e.stop();
			container = new Element('div', {
				'html': item.getProperty('rev')
			});
			container.getElements('input').each(function(el){
				el.set('name',el.get('name')+item.retrieve('nameiteration',1));
			});
			container.inject(item, 'before');
			item.store('nameiteration',item.retrieve('nameiteration',1)+1);
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

function doModalConfirm(url) {
	modalbg = new Element('div', {
		'class': 'modalbg'
	}).inject(document.body);
	modaldialog = new Element('div', {
		'class': 'modaldialog',
		'html':'<h2>Are You Sure?</h2><br />'
	}).inject(modalbg);
	buttonspc = new Element('div', {
		'class':'tar'
	}).inject(modaldialog);
	cancelbutton = new Element('input', {
		'type':'button',
		'value':'Cancel',
		events: {
			click: function(){
 				modalbg.fade('hide');
			}
		}
	}).inject(buttonspc);
	gobutton = new Element('input', {
		'type':'button',
		'value':'Yes, do it',
		events: {
			click: function(){
 				window.location = url;
			}
		}
	}).inject(buttonspc);

	modalbg.fade('hide');
	modalbg.fade('in');
}