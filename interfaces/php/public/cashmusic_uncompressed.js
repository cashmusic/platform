;window.cashmusic = (function() {
	'use strict';
	var cashmusic;
	if (window.cashmusic != null) {
		cashmusic = window.cashmusic;
	} else {
		cashmusic = {
			embed:function(publicURL, elementId, lightboxed, lightboxTxt) {
				var randomId = 'cashmusic_embed' + Math.floor((Math.random()*1000000)+1);
				var embedURL = publicURL + '/request/embed/' + elementId + '/location/' + encodeURIComponent(window.location.href.replace(/\//g,'!slash!'));
				if (lightboxed) {
					if (!lightboxTxt) {lightboxTxt = 'open element';}
					var overlayId = 'cashmusic_embed' + Math.floor((Math.random()*1000000)+1);
					document.write('<a id="' + randomId + '" href="' + embedURL + '" target="_blank">' + lightboxTxt + '</a><div id="' + overlayId + '" style="position:fixed;overflow:auto;top:0;left:0;width:100%;height:100%;background-color:rgba(80,80,80,0.85);opacity:0;display:none;z-index:654321;"><div style="position:absolute;top:80px;left:50%;margin-left:-260px;z-index:10;background-color:#fff;padding:10px;"><iframe src="' + embedURL + '" scrolling="auto" width="500" height="400" frameborder="0"></iframe></div></div>');
					var fadeEffect=(function(){
						return{
							init:function(id, flag, target) {
								this.elem = document.getElementById(id);
								clearInterval(this.elem.si);
								this.target = target ? target : flag ? 100 : 0;
								this.flag = flag || -1;
								this.alpha = this.elem.style.opacity ? parseFloat(this.elem.style.opacity) * 100 : 0;
								if (this.alpha == 0 && target > 0) {
									this.elem.style.display = 'block';
								}
								this.si = setInterval(function(){fadeEffect.tween();}, 20);
							},
							tween:function(){
								if(this.alpha == this.target) {
									clearInterval(this.elem.si);
								}else{
									var value = Math.round(this.alpha + ((this.target - this.alpha) * 0.05)) + (this.flag);
									this.elem.style.opacity = value / 100;
									this.elem.style.filter = 'alpha(opacity=' + value + ')';
									if (value == 0) {
										this.elem.style.display = 'none';
									}
									this.alpha = value;
								}
							}
						};
					}());

					document.getElementById(randomId).addEventListener('click', function(e) {
						fadeEffect.init(overlayId, 1, 100);
						e.preventDefault();
					}, false);

					window.addEventListener("keyup", function(e) { 
						if (e.keyCode == 27) {
							fadeEffect.init(overlayId, 0);
						} 
					}, false);
				} else {
					document.write('<iframe id="' + randomId + '" src="' + embedURL + '" scrolling="auto" width="100%" height="1" frameborder="0"></iframe>');
					var iframeEmbed = document.getElementById(randomId);
					
					    var onmessage = function(e) {
					    	if (embedURL.indexOf(e.origin) !== -1) {
					    		iframeEmbed.height = e.data + 'px';
					    		if (typeof window.addEventListener != 'undefined') {
									window.removeEventListener('message', onmessage, false);
								} else if (typeof window.attachEvent != 'undefined') {
									window.detachEvent('onmessage', onmessage);
								}
					    	}
						}
						if (typeof window.addEventListener != 'undefined') {
							window.addEventListener('message', onmessage, false);
						} else if (typeof window.attachEvent != 'undefined') {
							window.attachEvent('onmessage', onmessage);
						}
				}
			}
		};
	}
	return cashmusic;
}());