<?php

namespace IRC;

Class IRC
{
    protected $sock;

    public function __construct($address = false, $port = 6667)
    {
        $this->sock = new \IRC\Socket($address, $port);         //Create New Socket Object
    }

    public function connect($address, $port = 6667)
    {
        return $this->sock->connect($address, $port);           //Passthrough Function for Connecting
    }

    public function sendCommand($cmd, $params = array()) {      //Function to send an IRC command through
        if (count($params)) {
            $cmd .= " " . implode(" ", $params);                //Optionally appending Parameters
        }
        return $this->sock->send($cmd . "\r\n");
    }

    public function login($user, $pass = false) {
        if ($pass) {                                            //Helper function for logging in
            $this->sendCommand("PASS", array($pass));
        }
        
                                                                //This is using the New RFC IRC protocol
        $this->sendCommand("NICK", array($user));
        $this->sendCommand("USER", array($user, '0', '*', ':Bot Guy'));
    }

    public function join($room)
    {                                                           //Function for Joining room
        return $this->sendCommand("JOIN", array("#" . $room));
    }

    public function message($address, $msg)
    {                                                           //Function for Sending Message
        return $this->sendCommand("PRIVMSG", array($address, ":" . $msg));
    }

    protected function processBuffer($raw = "")
    {
        $d = $this->sock->read();                               //Get the latest from the socket
        if ($d !== false) {
            $raw .= $d;                                         //Append the data to $raw
        } else {
            return false;                                       //Unless there was an error in which case return false
        }
        if (substr($raw, -2) === "\r\n") {                      //Check that the data ends on a command
            $raw = explode("\r\n", $raw);
            $messages = array();
            foreach ($raw as $message) {
                if ($message) {                                 //If the data ended nicely process it into messages
                    array_push($messages, new \IRC\Message($message));
                }
            }
            return $messages;                                   //Return the list of message objects
        } else {
            sleep(1);
            return $this->processBuffer($raw);                  //If it ended in the middle, loop around
        }
    }

    public function pollForData()
    {                                                           //Function to Poll for Data
        if ($this->sock->hasDataToRead()) {
            return $this->processBuffer();                      //If it has data, proccess it
        } else {
            sleep(1);
            return $this->pollForData();                        //Otherwise loop
        }
    }
}