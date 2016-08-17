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
    public $reportable_orders, $report;

    public function __construct($user_id, $connection_id) {
        $this->settings_type = 'com.soundscan';
        $this->user_id = $user_id;
        $this->connection_id = $connection_id;
        $this->redirects = false;

        if ($this->getCASHConnection()) {

            $connections = CASHSystem::getSystemSettings('system_connections');
            if (isset($connections['com.soundscan'])) {
                $this->client_id = $connections['com.soundscan']['client_id'];
            }

        } else {
            $this->error_message = 'could not get connection settings';
        }
    }

    public function addApplicableOrders($orders) {

        $formatted_orders = [];
        foreach($orders as $order) {
            $formatted_orders[] = [
                'something' => $order['something'],
                'something_else' => $order['something_else']
            ];
        }

        return $this;
    }

    public function createReport() {

        return $this;
    }

    public function sendReport() {

        return $this;
    }
}