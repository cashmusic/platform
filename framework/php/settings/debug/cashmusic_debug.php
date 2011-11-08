<?php 
	$originals = array("Array\n(\n",'  ',"\n",'<br />)');
	$replacements = array('','&nbsp;&nbsp;','<br />','');
	//var_dump($cash_primary_request);
?>
<script type="text/javascript">
	function cashShowHideDebugPanel (showOrHide) {
		var panel = document.getElementById("cash_debug_panel");
		var tab = document.getElementById("cash_debug_tab");
		if (showOrHide == 'show') {
			panel.style.display="block";
			tab.style.display="none";
		} else {
			panel.style.display="none";
			tab.style.display="block";
		}
	}
</script>
<div id="cash_debug_tab" style="font:12px/1.5em 'Helvetica Neue',Helvetica,Arial,sans-serif;position:absolute;z-index:4321;top:0;right:445px;width:95px;height:auto;color:#fff;padding:24px 28px 28px 28px;opacity:0.92;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;background:transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALQAAABkCAYAAAAv8xodAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAW5JREFUeNrs11FmA1EYhuGZykpmC7OQUjrCUMqQReS2m2gIuQodpZQu42yoxOQc5qwgqvF7Hn65m4vPa0zaruuWBoJ4MAGCBkGDoEHQCBoEDYIGQYOgETQIGgQNggZBI2gQNAgaBA2CRtAgaBA0CBoEjaBB0CBoEDQIGkGDoEHQIGgQNIIGQYOgQdAIGgQNggZBg6ARNAgaBA2CBkEjaBA0CBoEDYJG0CBoEDQIGgSNoEHQIGgQNAgaQYOgQdAgaBA0ggZBg6BB0CBoBA2CBkGDoBE0CBoEDX9p0/f9Lv++3/qglJI1uYs39CHfzhSECHqe52aNejIHkb6hj/leTUKkP4WnNeqLaYgQdI16EjVRgq5Rv4iaKEEXZ1ETKega9ShqogRdfOTbipooQRefa9S/JiNC0DXqUdRECdqbmnBBF1/5nkVNlKCLb1ETKega9ZOoiRJ08ZPvUdTci3ZZlpsfMgzDPqX0Zk7+21WAAQBKUix9rXDaFQAAAABJRU5ErkJggg==) left bottom no-repeat;">
	<a href="#" onclick="cashShowHideDebugPanel('show');return false;" id="cash_hide_debug_panel_link" style="font:10px/1.25em 'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:bold;margin:0;color:#aacd07;">show debug panel</a>
</div>
<div id="cash_debug_panel" style="font:12px/1.5em 'Helvetica Neue',Helvetica,Arial,sans-serif;display:none;position:absolute;z-index:4321;top:0;right:60px;width:480px;height:auto;color:#fff;padding:28px;opacity:0.92;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;background-color:#222;margin-bottom:36px;">
	<div style="margin:0;padding:0;position:relative;z-index:100;">
	<h3 style="font:14px/1.25em 'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:bold;margin:0;">last platform response:</h3>
	<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:10px;"><?php echo str_replace($originals,$replacements,htmlspecialchars(print_r($_SESSION['cash_last_response'],true))); ?></div>
	<br /><br />
	<h3 style="font:14px/1.25em 'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:bold;margin:0;">persistent data:</h3>
	<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:10px;"><?php echo str_replace($originals,$replacements,htmlspecialchars(print_r($_SESSION['cash_persistent_store'],true))); ?></div>
	<br /><br />
	<a href="#" onclick="cashShowHideDebugPanel('hide');return false;" id="cash_hide_debug_panel_link" style="font:10px/1.25em 'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:bold;margin:0;color:#aacd07;">hide debug panel</a>
	</div>
	<img style="position:absolute;bottom:-18px;left:0;z-index:10;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAhgAAAASCAYAAAAZgKdfAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAO5JREFUeNrs2MFmA1EUx+GbvmAIM0IIIdqH6DYvkVK6KhkhlD7GfaFSk3uYS/Y5y+/jz6zu4qx+ZjUMw2sp5VyeVGstAADhpe2j7c0pAIC0wJimqSyRcXQOACAlMB6+P9sOTgIAZAZG+Foi499pAICswOiRcRQZAEBmYPTI2IsMACAzMMK3yAAAsgOjR8ZOZAAAmYERLm1bkQEAZAZGuC6R8edkAEBWYPTI2IkMACAzMHpk+JMBAKQGRri1DSIDAMgMjPAjMgCA7MDokbERGQBAZmCE37a1yAAAHq3meX76kXEc32utJ+cEAMJdgAEABNAq115lhbUAAAAASUVORK5CYII=" alt="" />
</div>