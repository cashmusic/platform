Ứng dụng quản trị cho nền tảng (**/interfaces/admin**) là một webapp được xây dựng khá trực tiếp theo phong cách MVC với một controller trước tiên, các controller riêng lẻ cho từng route, các mustache views, và sử dụng framework cho model thay vì lớp cơ sở dữ liệu truyền thống. Nói đơn giản thì nó là 1 minh chứng cho khả năng ứng dụng của PHP Core và còn xây dựng 1 ứng dụng phức tạp hơn là 1 thành phần đơn giản.

Về mặt cấu trúc, nó khá đơn giản: 

 - Cấu hình được lưu trữ trong tập tin **constants.php**
 - Tập tin .htaccess đẩy toàn bộ lưu lượng truy cập thông qua tập tin **controller.php**
 - Mỗi route đều có 1 controller trong **/components/pages/controllers** và sau khi thực thi bất kì logic nào controller đều gọi một mustache template view từ **/components/pages/views**
 - UI(Giao diện) của trang chủ được lưu trữ trong các mustache templates nằm ở **/ui/default**

Không nên quên rằng ứng dụng quản trị này được cấu trúc để phản ánh các loại Yêu cầu/Phản hồi CASH— điều này có mục đích chủ yếu là để các nhạc sĩ và nhà phát triển giao tiếp cùng một ngôn ngữ. 
