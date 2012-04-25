![CASH Music Admin](https://cashmusic.s3.amazonaws.com/permalink/images/readme_admin_.jpg)

The CASH admin app is a standalone webapp that interfaces with the CASH framework. 
All relevant files are located in the repo at /interfaces/php/admin/  

The dev installer will set up the admin app, but for a manual install you need a 
working install of the framework. The RewriteBase value in the admin .htaccess 
file will need to reflect the true web root of the admin, and the $cashmusic_root 
and ADMIN_WWW_BASE_PATH values in constants.php need to point to real framework / 
www locations.  


##Admin App Structure  
All of the heavy lifting is handled in the framework, so the app itself 
is minimal. The .htaccess file routes all requests to a modest front controller 
([controller.php](controller.php)) which parses the incoming URL pushes to a 
page-specific view/controller and ultimately into a UI template. 


##Admin Core Classes  
The /interfaces/php/admin/classes folder contains two different classes for working 
with the admin — AdminCore which needs to be instantiated and a static helper class 
called AdminHelper. Both are auto-loaded — an instance of AdminCore is available to 
all scripts as $cash_admin, and AdminHelper is included in the path for 
AdminHelper::function() calls. 


##Admin URL Structure  
Page scripts/markup are stored in /interfaces/php/admin/components/pages — the 
'definitions' directory contains a script for each page to process requests, and 
'markup' stores the guts of the page for rendering the output. Script names follow 
the URL patterns with underscores instead of slashes (/assets/add = assets_add.php) 
— the controller selects the appropriate definition/markup based on the URL and 
POSTS all remaining parts of the URL as postname1/value1/postname2/value2, etc.

CASH framework request responses are passed from the definition to the markup 
using a simple storage method in AdminCore, like so:  
  
```php
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'type', 
		'cash_action' => 'action',
		'details' => 'value'\
	),
	'storedresponse'
);

$cash_admin->getStoredResponse('addasset');
```  
  
Any other needed variables will persist in the global scope as we're just using 
includes.  