<?php

/**
 * The Sound Scan class creates a report from passed orders,
 * then uploads the report to their FTP via CASH Daemon scheduling
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
    public $orders, $report;

    public function __construct() {
        //
    }

    public function addOrders() {

        // get last timestamp for soundscan process
        $timestamp = 0;

        // get external fulfillment orders after that timestamp
        //TODO: needs to be filtered by country
        if ($orders = ExternalFulfillmentSeed::getOrders($timestamp)) {

            // loop through the orders and format them to match the soundscan report structure
            $formatted_orders = [];
            foreach($orders as $order) {
                $formatted_orders[] = implode("|", $order);
            }

            $this->orders = implode("\n", $formatted_orders);
        } else {

            // no orders found, should probably return an error here
            return false;
        }



        return $this;
    }

    public function createReport() {

        // grab some shit and concatenate into a report
        $this->report = "header|shit|i|dunno\n"
        .$this->orders;

        return $this;
    }

    public function sendReport() {

        if (!CASHSystem::uploadStringToFTP($this->report, "testfilename39unjsdkn.txt", [
            'domain' => 'supermegaawesomeftp.terrorbird.com',
            'username' => 'terrorbird',
            'password' => 'xLQV$bVO2GIWbWe'
        ])) {
            // something fucked up
            error_log(
                'something fucked up'
            );
        }

        return $this;
    }
}