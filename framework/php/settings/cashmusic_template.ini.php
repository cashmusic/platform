; <?php die(); __halt_compiler();
;
; Standard Seed configuration file
; (note the first line. php opener/die included to prevent dumping settings to browser)
;
; Copyright (c) 2012, CASH Music
; Licensed under the Affero General Public License version 3.
; See http://www.gnu.org/licenses/agpl-3.0.html

[database_connection]
driver = "mysql" ;* sqlite or mysql
hostname = "127.0.0.1:8889"
username = "root"
password = "root"
database = "cashmusic"

[security]
salt = "I was born of sun beams; Warming up our limbs" ;* DO NOT CHANGE, SRSLY

[core]
debug = no

[api]
apilocation = "http://localhost:8888/interfaces/php/api/"

[system]
instancetype = "single" ;* single or multi
timezone = "US/Pacific" ;* PHP timezone format

[email]
systememail = "CASH Music <info@cashmusic.org>"
smtp = no
smtpserver = ""
smtpport = 587
smtpusername = ""
smtppassword = ""