; <?php die(); __halt_compiler();
;
; Admin configuration file
; Settings here override settings in the constants.php file, allowing you to set
; them separately in a deploy that depends on the full platform repo.
;
; Copyright (c) 2015, CASH Music
; Licensed under the Affero General Public License version 3.
; See http://www.gnu.org/licenses/agpl-3.0.html

[admin_options]
allow_signups 				= false
cdn_url						= "https://cashmusic.org/admin"
jquery_url					= "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"
jqueryui_url 				= "//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"
minimum_password_length = 10
subdomain_usernames 		= true
show_beta			 		= false
