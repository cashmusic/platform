<?php
// add unique page settings:
$pageTitle = 'Assets: Add Assets';

$pageTips = 'There’s a pretty huge overlap between assets and content. So the goal here should be placed on adding new assets, finding existing assets, editing metadata, and setting permissions for things. Display of any assets should be largely handled in the content section.';

$pageContent = <<<PAGECONTENT

<div id="sectionmenu">
<a href="/assets/find/">find assets</a> <a href="/assets/add/">add assets</a> <a href="/assets/edit/">edit assets</a>
</div>

<br /><br />

<h3>Choose An Asset Type</h3>
<p>
Ad hoc asset<br />
Collection<br />
Release
</p><p>
Add to an existing collection/release (link to edit...)
</p>

PAGECONTENT;
?>