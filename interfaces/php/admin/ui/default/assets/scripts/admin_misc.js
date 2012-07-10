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
		'class':'button',
		'value':'Cancel',
		events: {
			click: function(){
 				modalbg.fade('out');
			}
		}
	}).inject(buttonspc);
	gobutton = new Element('input', {
		'type':'button',
		'class':'button',
		'value':'Yes, do it',
		events: {
			click: function(){
 				window.location = url + '?modalconfirm=1';
			}
		}
	}).inject(buttonspc);

	modalbg.set('tween', {duration: 'short'});
	modalbg.fade('hide');
	modalbg.fade('in');
}