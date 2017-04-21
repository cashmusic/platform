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

    public function send($subject,$message_text,$message_html,$from_address,$from_name,$sender_address,$recipients,$metadata=null,$global_merge_vars=null,$merge_vars=null,$tags=null) {
        $swift = Swift_Mailer::newInstance($this->transport);

        $message = new Swift_Message($subject);
        $message->setFrom([$sender_address => $from_name]);
        $message->setReplyTo([$from_address => $from_name]);
        //	$message->setSender($sender);
        $message->setBody($message_html, 'text/html');
        $message->setTo($recipients);
        $message->addPart($message_text, 'text/plain');
        $headers = $message->getHeaders();
        $headers->addTextHeader('X-MC-Track', 'opens'); // Mandrill-specific tracking...leave in by defauly, no harm if not Mandrill

        if ($recipients = $swift->send($message, $failures)) {
            return true;
        } else {
            return false;
        }
    }
}

?>