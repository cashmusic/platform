<form method="post" action="">
<div class="col_onehalf">
	<h3>Element Details</h3>
		
	<label for="text1">Name</label><br />
	<input type="text" id="text1" /> 
	
	<div class="row_seperator">.</div><br />
	
	<label for="text2">Invalid Email Error Message</label><br />
	<input type="text" id="text2" value="Sorry, that email address wasn't valid. Please try again." />
	
	<label for="text3">Privacy Message</label><br />
	<input type="text" id="text3" value="We won't share, sell, or be jerks with your email address." />	
</div>

<div class="col_onehalf lastcol">
	<h3>&nbsp;</h3>
	<label for="text1">The Downloadable Asset</label><br />
	<select id="select1">
		<option>“You Wouldn’t Have To Ask” MP3</option>
	</select>
	<br /><br />
	
	<a href="<?php echo WWW_BASE_PATH; ?>/assets/add/"><small>ADD NEW ASSET</small></a>
	
	<div class="row_seperator">.</div><br />
	
	<label>Comment Or Agreement</label><br />
	<input type="radio" name="radio1" class="checkorradio" checked="checked" /> Neither &nbsp; &nbsp; <input type="radio" name="radio1" class="checkorradio" /> Comment &nbsp; &nbsp; <input type="radio" name="radio1" class="checkorradio" /> Agreement 
</div>
<div class="row_seperator">.</div><br />
<div class="tar">
	<input class="button" type="submit" value="Add That Element!" />
</div>

</form>