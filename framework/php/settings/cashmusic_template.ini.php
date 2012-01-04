; <?php die(); __halt_compiler();
;
; Standard Seed configuration file
; (note the first line. php opener/die included to prevent dumping settings to browser)
;
; Copyright (c) 2011, CASH Music
; Licensed under the Affero General Public License version 3.
; See http://www.gnu.org/licenses/agpl-3.0.html

[database_connection]
driver = "mysql"
hostname = "localhost:8889"
username = "root"
password = "root"
database = "seed"

[security]
salt = "I was born of sun beams; Warming up our limbs"

[core]
debug = 0

[api]
apilocation = "http://localhost:8888/interfaces/php/api/"

[system]
systememail = "CASH Music <info@cashmusic.org>"
timezone = "US/Pacific"