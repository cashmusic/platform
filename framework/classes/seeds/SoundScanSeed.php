<?php

/**
 * The Soundscan class creates a report from passed orders,
 * then uploads the report to their FTP via CASH Daemon scheduling
 *
 * Internet/Mail Order (IMO) runs on a Tuesday-Monday reporting schedule with reports due in by 1:00pm ET on Tuesdays.
 * Digital runs Friday-Thursday, with reports due in no later than 1:00pm ET on Fridays.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 **/
class SoundScanSeed extends SeedBase
{
    private $chain_number, $account_number, $end_date, $orders, $report_type, $ftp_domain, $ftp_user, $ftp_password, $filename;
    public $report;

    public function __construct($orders, $end_date, $report_type) {

        $this->report = '';
        $this->total_items = 0;

        $this->end_date = $end_date;
        $this->orders = $orders;

        $this->report_type = $report_type ? $report_type : "physical";

        // get account identifiers and ftp information
        $connections = CASHSystem::getSystemSettings('system_connections');
        if (isset($connections['com.soundscan'])) {
            
            $c = $connections['com.soundscan'];

            $this->chain_number = $c['chain_number'];
            $this->ftp_domain = $c['ftp_domain'];

            // weird physical/digital dance of doom
            if ($this->report_type == "physical") {
                $this->account_number = $c['imo_account_number'];
                $this->filename = $c['imo_filename'];

                $this->ftp_user = $c['imo_ftp_user'];
                $this->ftp_password = $c['imo_ftp_password'];
            }

            if ($this->report_type == "digital") {
                $this->account_number = $c['digital_account_number'];
                $this->filename = $c['digital_filename'];

                $this->ftp_user = $c['digital_ftp_user'];
                $this->ftp_password = $c['digital_ftp_password'];

                // reformat the orders to make digital reporting not as dumberly
                $this->formatDigitalOrders();
            }

        } else {
            error_log(
                "Couldn't find Soundscan settings"
            );
            return false;
        }
    }

    private function addHeader() {
        // first 2 characters are record type "92"
        // chain number and account number are assigned by Nielsen
        // end date in YYMMDD format
        $this->report .= "92" . $this->chain_number . $this->account_number . $this->end_date . "\n";

        return $this;
    }

    private function addHeaderDigital() {
        // NOT pipe delimited
        // first 2 characters are record type "92"
        // chain number and account number are assigned by Nielsen
        // end date in YYMMDD format
        // filler spaces
        $this->report .= "92" . $this->chain_number . $this->account_number . $this->end_date
            . '				     ' . "\n";

        return $this;
    }

    private function parseOrders() {
        foreach ($this->orders as $order) {
            // first 2 characters of each line are record type "M3"
            // loop through each order and dump UPC and zip
            // end each line with sale ("S") or return ("R")
            $this->report .= "M3" . $order['upc'] . $this->stripPostalCode($order['postal']). "S\n";
        }

        return $this;
    }

    private function parseOrdersDigital() {

        //foreach ($this->orders as $order) {
            // pipe delimited
            // first 2 characters of each line are record type "D3"
            // loop through each order, then contents, and dump
            // D3 | UPC (if album) | ZIP | S/R (sale/return) | # in order (3 digits, left pad) | ISRC (if single) | PRICE | S/A (single/album) | P/M (PC/web or Mobile)
            $itemcount = 1;
            foreach ($this->orders as $item) {
                $output = "D3" . "|";
                if ($item[0] == 'A') {
                    $output .= $item[1] . "|" . $this->stripPostalCode($item[2]) . "|S|" . str_pad($itemcount,3,'0',STR_PAD_LEFT) . "||" . $item[3] . "|" . $item[0] . "|P\n";
                } else {
                    $output .= "|" . $item[2] . "|S|" . str_pad($itemcount,3,'0',STR_PAD_LEFT) . "|" . $item[1] . "|" . $item[3] . "|" . $item[0] . "|P\n";
                }
                $this->report .= $output;
                $this->total_items++;
                //$itemcount++;
            //}
        }

        return $this;
    }


    private function addFooter() {
        // first 2 characters are record type "94"
        // left-padded total transactions
        // left-padded total units (net, actually...but fuck a return)
        $this->report .= "94" . str_pad(count($this->orders),5,'0',STR_PAD_LEFT) . str_pad(count($this->orders),7,'0',STR_PAD_LEFT) . "\n";

        return $this;
    }

    private function addFooterDigital() {
        // first 2 characters are record type "94"
        // left-padded total transactions
        // left-padded total units (net, actually...but fuck a return)
        $this->report .=  "94|" . $this->total_items . "|" . $this->total_items . "\n";

        return $this;
    }

    public function createReport() {

        // grab some shit and concatenate into a report

        if ($this->report_type == "physical") {
            $this->addHeader()
                ->parseOrders()
                ->addFooter();
        }

        if ($this->report_type == "digital") {
            $this->addHeaderDigital()
                ->parseOrdersDigital()
                ->addFooterDigital();
        }


        return $this;
    }

    public function sendReport() {

        // just test this shit for now
        echo nl2br($this->report);
        //file_put_contents("/var/www/".$this->filename, $this->report);

        CASHSystem::sendEmail(
            $this->report_type.' Soundscan report run.',
            1,
            'tom@cashmusic.org',
            nl2br($this->report),
            $this->report_type.' Soundscan report run.'
        );

        CASHSystem::sendEmail(
            $this->report_type.' Soundscan report run.',
            1,
            'jesse@cashmusic.org',
            nl2br($this->report),
            $this->report_type.' Soundscan report run.'
        );

        CASHSystem::sendEmail(
            $this->report_type.' Soundscan report run.',
            1,
            'chris@cashmusic.org',
            nl2br($this->report),
            $this->report_type.' Soundscan report run.'
        );


        /*        if (!CASHSystem::uploadStringToFTP($this->report, $this->filename, [
            'domain' => $this->ftp_domain,
            'username' => $this->ftp_user,
            'password' =>$this->ftp_password
        ], "sftp")) {
            // something did not work out right
            error_log(
                'omg lol rotfl'
            );
        }*/

        return $this;
    }

    private function formatDigitalOrders() {

        foreach ($this->orders as $order) {
            // album(A) or single(S), ISRC/UPC, zip (no +4. first 5 digits only), price (4 digits in pennies)
            $orders_formatted[] = [
                'A',
                $order['upc'],
                $order['postal'],
                $order['price']
            ];
        }

        $this->orders = $orders_formatted;
    }

    private function stripPostalCode($postal) {
        // USA USA USA USA USA USA!!!!
        return substr($postal, 0, 5);
    }
}