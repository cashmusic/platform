![CASH Music Admin](https://cashmusic.s3.amazonaws.com/permalink/images/readme_admin.jpg)

The CASH admin app is a standalone webapp that interfaces with the CASH framework. It 
gives a person using the framework an easy way to log in, work with their data, 
define new elements, and check usage.

The dev installer will set up the admin app, but for a manual install you need a 
working install of the framework. The RewriteBase value in the admin .htaccess 
file will need to reflect the true web root of the admin, and the $cashmusic_root 
and ADMIN_WWW_BASE_PATH values in constants.php need to point to real framework / 
www locations.  


##Admin App Workflow  
All of the heavy lifting is handled in the framework, so the app itself is minimal.

The .htaccess file routes all requests to a modest front controller 
([controller.php](https://github.com/cashmusic/DIY/blob/master/interfaces/php/admin/controller.php)) 
which parses the incoming URL pushes to a page-specific view/controller and ultimately 
into a mustache-based UI template. There is no direct database access, with all 
significant functionality happening in the form of Requests/Responses passed to 
and from the CASH framework.


##Admin App Structure  
A quick look at the files in the app: 

 - **/assets** <br /> UI-independent images (connection logos and a forward script for element headers)
 - **/classes** <br /> The main AdminCore class and an AdminHelper comprised of static functions
 - **/components**
   - **/menu** <br /> Stores the site structure in JSON, used to build navigation menus
   - **/pages** <br /> Views/controllers for each unique page — controllers are optional
   - **/text** <br /> Page tips and help section text, separated by language code
 - **/lib** <br /> External libraries (currently just Mustache.php)
 - **/ui** <br /> Mustache templates, assets, and CSS for the main UI - multiple UIs are 
   possible and should follow the pattern of the default UI template


##Admin Core Classes  
The ([/interfaces/php/admin/classes](https://github.com/cashmusic/DIY/blob/master/interfaces/php/admin/classes)) 
directory contains two different classes for working with the admin — AdminCore which 
needs to be instantiated and a static helper class called AdminHelper. Both are auto-
loaded — an instance of AdminCore is available to all scripts as $cash_admin, and 
AdminHelper is included in the path for AdminHelper::function() calls. 


##Admin URL Structure  
Page views/controllers are stored in 
([/interfaces/php/admin/components/pages](https://github.com/cashmusic/DIY/blob/master/interfaces/php/admin/components/pages)). 
Views are currently plain PHP but will be converted to mustache for true separation of 
logic and presentation. Script names follow the URL patterns with underscores instead 
of slashes (/assets/add = assets_add.php.) 

The controller matches routes to filenames, but also allows data to be passed to 
a controller using a non-get URL. Any path data sent to a page will be parsed and 
added to a $request_parameters array in the order received. so a route like 
/assets/edit/7 would call the assets_edit.php controller with $request_parameters[0] = 7.

CASH framework request responses are currently passed from the controller to view
at the page level using a simple storage method in AdminCore, like so:  
  
```php
<?php
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'type', 
		'cash_action' => 'action',
		'details' => 'value'\
	),
	'storedresponse'
);

$cash_admin->getStoredResponse('addasset');
?>
```  
  
