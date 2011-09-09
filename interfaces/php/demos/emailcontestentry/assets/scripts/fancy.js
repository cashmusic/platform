// quick array of dates / locations to use as the comment
// these are also element names for the links
var fadeplease = new Array('sep08van','sep09pdx','sep10sea');
window.addEvent('domready', function(){
	// add onclick events for each date
	fadeplease.each(function(item){
		document.id(item).addEvent('click', function(){
			fadeandhighlight(item);
		});
	});
	
	if (document.id('cash_emailcollection_form_101')) {
		document.id('cash_emailcollection_form_101').addEvent('submit', function(e){
			// this whole block handles the check to see if an email has been set
			// and verifies that a date has been selected. if not it shows an error
			// message and gives instructions
			var commentInput = document.id('cash_emailcollection_form_101').getFirst('input.cash_input_comment');
			var emailInput = document.id('cash_emailcollection_form_101').getFirst('input.cash_input_address');
			
			if (commentInput.get('value') == '' || emailInput.get('value').trim() == '') {
				e.stop();
				document.id('msgspc').setStyles({
					'display':'block',
					'opacity':0.9
				});
				document.id('msgspc').set('text','please enter your email and chose a city before submitting');
				(function () {
					document.id('msgspc').tween('opacity',0);
				}).delay(2000);
			}
		});
	}
});

function fadeandhighlight(highlightel) {
	// fades back all of the other dates, sets the comment in the form
	document.id(highlightel).setStyle('opacity',1);
	document.id('cash_emailcollection_form_101').getFirst('input.cash_input_comment').set('value',highlightel);
	fadeplease = fadeplease.erase(highlightel);
	fadeplease.each(function(item){
		document.id(item).tween('opacity',0.35);
	});
	fadeplease.push(highlightel);
}

