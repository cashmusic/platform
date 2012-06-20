## Admin app
The admin app for the platform (/interfaces/php/admin) is a fairly straight-forward MVC-style webapp 
built with a front controller, individual controllers for each route, mustache views, and using the 
framework for the model instead of a traditional database layer. Basically it's dog-fooding the PHP 
API but building a much more complex app than a simple element. 

Like the framework, the admin app is built with minimal requirements, not using any more robust PHP 
app frameworks. The reason for this decision was the distributed version of the platform — what we 
lose in functionality we gain in portability, which is a major concern. 

In terms of structure, it's fairly simple: 

 - At the root there's an .htaccess redirect pointing all incoming traffic at controller.php, which 
 pulls in settings from constants.php 
 - A page is rendered based on pieces found in the /components directory
 - Each page/route has a controller in /components/pages/controllers and the controller calls a 
 mustache template from /components/pages/templates
 - We're in the process of moving localized text to the /components/text directory, and similarly 
 menus are pulled out by language in the /components/menu directory
 - Two simple helper classes are found in the /classes directory
 - The main page UI is a simple mustache-based template found in /ui/default — there's not currently 
 a setting in the admin to allow theming but it's essentially just a path change away   

Currently the admin is more of a code-wrapper than an application with elegant UI/UX. Our prime 
focus is improving that, making each section of the admin feel intuitive and easy as well as adding 
more help infrastructure. 

It shouldn't be lost that the admin app is structured to mirror the Request/Response types — this is 
very much on purpose with the goal of getting musicians and developers speaking the same language. 

Also, Jackson.