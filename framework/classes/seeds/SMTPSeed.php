<?php

namespace CASHMusic\Seeds;

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use CASHSystem;
use SeedBase;

class SMTPSeed extends SeedBase {

    protected $server, $email, $port, $username, $password;

    public function __construct($server=false, $email=false, $port=false, $username=false, $password=false) {

        $settings = CASHSystem::getDefaultEmail(true);

        // SMTP settings are set; make sure we have the necessary values and assign, or die
        if ($settings['smtp'] &&
            isset($settings['smtpserver'],
                $settings['systememail'],
                $settings['smtpport'],
                $settings['smtpusername'],
                $settings['smtppassword'])) {

            $this->server = $settings['smtpserver'];
            $this->email = $settings['systememail'];
            $this->port = $settings['smtpport'];
            $this->username = $settings['smtpusername'];
            $this->password = $settings['smtppassword'];
            
        } else if (isset($server, $email, $port, $username, $password)) {

        } else {
            return false;
        }

        $this->transport = Swift_SmtpTransport::newInstance($this->server, $this->port);

        $this->transport->setUsername($this->username);
        $this->transport->setPassword($this->password);
    }

    public function send($subject,$message_txt,$message_html,$from_address,$from_name,$recipients,$metadata=null,$global_merge_vars=null,$merge_vars=null,$tags=null) {

    }
}

?>