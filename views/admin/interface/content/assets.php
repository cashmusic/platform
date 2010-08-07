<?php
// add unique page settings:
$pageTitle = 'Assets: Main';

$pageTips = 'There’s a pretty huge overlap between assets and content. So the goal here should be placed on adding new assets, finding existing assets, editing metadata, and setting permissions for things. Display of any assets should be largely handled in the content section.';

$pageContent = <<<PAGECONTENT

<div id="sectionmenu">
<a href="/assets/find/">find assets</a> <a href="/assets/add/">add assets</a> <a href="/assets/edit/">edit assets</a>
</div>

<br /><br />

<div class="col_onethird">
<h3>Quick Asset Search</h3>
<form>
	<label for="text1">Title / Tags</label><br />
	<input type="text" id="text1" /> 
	<input class="button" type="submit" value="Search" />
</form>
</div>
<div class="col_twothirds lastcol">
	<h3>At A Glance</h3>
	Most recent assets here? Quick add asset page?<br /><br />
	Shouldn't be super-vital. Replace this content with quick search results...bad ui?
</div>

PAGECONTENT
?>