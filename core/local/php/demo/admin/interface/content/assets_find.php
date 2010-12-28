<div id="sectionmenu">
<a href="/assets/find/">find assets</a> <a href="/assets/add/">add assets</a> <a href="/assets/edit/">edit assets</a>
</div>

<br /><br />

<div class="col_twothirds">
	<h3>Find Assets</h3>
	<form>	
	<label for="text1">Search Terms</label><br />
	<input type="text" id="text1" /> 

	<div class="row_seperator">.</div>
	<label>Search For</label><br />
	<div class="col_onefourth">
	<input type="checkbox" class="checkorradio" checked="checked" /> Title
	</div>
	<div class="col_onefourth">
	<input type="checkbox" class="checkorradio" checked="checked" /> Tag
	</div>
	<div class="col_onefourth">
	<input type="checkbox" class="checkorradio" /> All Metadata
	</div>
	
	<div class="row_seperator">.</div>
	<div class="col_onehalf">
		<label>Limit by Date</label><br />
		The asset was first created<br />
		<input type="radio" name="radio1" class="checkorradio" checked="checked" /> Before &nbsp; &nbsp; <input type="radio" name="radio1" class="checkorradio" /> After 
		<div class="col_onehalf">
			<input type="text" />
		</div>
	</div>
	<div class="col_onehalf lastcol">
		<label>Limit by Use</label><br />
		Total times the asset has been accessed<br />
		<input type="radio" name="radio2" class="checkorradio" checked="checked" /> At least &nbsp; &nbsp; <input type="radio" name="radio2" class="checkorradio" /> At most 
		<div class="col_onehalf">
			<input type="text" />
		</div>
	</div>
	
	<div class="row_seperator">.</div><br />
	<input class="button" type="submit" value="Search" />
	
	</form>
</div>

<div class="col_onethird lastcol">
	<h3>Saved Searches</h3>
	<p>
	Links for system defaults like 'last 20 assets added', 'most accessed assets', 'least accessed assets',
	etc. Also add user saved searches. But all this is a low priority.
	</p>
</div>