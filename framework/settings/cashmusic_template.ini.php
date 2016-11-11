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
hostname = "localhost"
username = "cash_dev_rw"
password = ""
database = "cash_dev"

[security]
salt = "I was born of sun beams; Warming up our limbs" ;* DO NOT CHANGE, SRSLY

[core]
debug = no

[api]
apilocation = "https://vagrant-multi1.cashmusic.org/api/"
venues_api = "https://venues.cashmusic.org"

[system]
instancetype = "multi" ;* single or multi
timezone = "US/Pacific" ;* PHP timezone format
analytics = "full"      ;* full or basic or off

[email]
systememail = "CASH Music <info@cashmusic.org>"
smtp = no
smtpserver = ""
smtpport = 587
smtpusername = ""
smtppassword = ""
