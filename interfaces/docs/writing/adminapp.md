The admin app for the platform (**/interfaces/admin**) is a fairly straight-forward MVC-style webapp 
built with a front controller, individual controllers for each route, mustache views, and using the 
framework for the model instead of a traditional database layer. Basically it's dog-fooding the PHP 
core but building a much more complex app than a simple element. 

Về mặt cấu trúc, nó khá đơn giản: 

 - Cấu hình được lưu trữ trong tập tin **constants.php**
 - Tập tin .htaccess đẩy toàn bộ lưu lượng truy cập thông qua tập tin **controller.php**
 - Mỗi route đều có 1 controller trong **/components/pages/controllers** và sau khi after doing any logic the 
 controller calls a mustache template view from **/components/pages/views**
 - The main page UI is stored in mustache templates in **/ui/default**

It shouldn't be lost that the admin app is structured to mirror the CASH Request/Response types — this is 
very much on purpose with the goal of getting musicians and developers speaking the same language. 
