; <?php die(); __halt_compiler();
;
; Standard Seed configuration file
; (note the first line. php opener/die included to prevent dumping settings to browser)
;
; Copyright (c) 2010, CASH Music
; Licensed under the Affero General Public License version 3.
; See http://www.gnu.org/licenses/agpl-3.0.html

[database_connection]
hostname = ""
username = ""
password = ""
database = ""

[paypal_primary_account]
paypal_address = ""
paypal_key = ""
paypal_secret = ""

[paypal microtransaction account]
; use primary account settings if no microtransaction account
paypal_micro_address = ""
paypal_micro_key = ""
paypal_micro_secret = ""

[other paypal settings]
smallest_allowed_transaction = 

[s3_settings]