The admin app for the platform (**/interfaces/admin**) is a fairly straight-forward MVC-style webapp 
built with a front controller, individual controllers for each route, mustache views, and using the 
framework for the model instead of a traditional database layer. Basically it's dog-fooding the PHP 
core but building a much more complex app than a simple element. 

In terms of structure, it's fairly simple: 

 - Settings are stored in the **constants.php** file
 - The .htaccess pushes all traffic through the **controller.php** file
 - Each route has a controller in **/components/pages/controllers** and after doing any logic the 
 controller calls a mustache template view from **/components/pages/views**
 - The main page UI is stored in mustache templates in **/ui/default**

It shouldn't be lost that the admin app is structured to mirror the CASH Request/Response types — this is 
very much on purpose with the goal of getting musicians and developers speaking the same language. 