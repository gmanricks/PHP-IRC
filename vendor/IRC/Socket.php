<?php

namespace IRC;

Class Socket
{
    protected $sock;
    protected $status;

    public function __construct($address = false, $port = 6667)
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, 0);   //Instantiate Socket
                                                                
        socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_clear_error($this->sock);
        socket_set_nonblock($this->sock);                       //Set some defaults

        
        $this->status = "Created";

        if ($address) {                                         //If the address was passed in call connect
            $this->connect($address, $port);
        }
    }

    public function connect($address, $port = 6667)
    {
        if (@socket_connect($this->sock, $address, $port) === true) {
            $this->status = "Connected";
            return true;
        } else {
            $errNum = socket_last_error($this->sock);
            if (in_array($errNum, array(106, 133, 56))) {       //Check if socket is already connected
                $this->status = "Connected";
                return true;
            } elseif ($this->isBusy()) {                        //Check if the connection is busy
                sleep(1);
                return $this->connect($address, $port);
            } else {                                            //Socket Error
                $this->status = "Error";
                return false;
            }
        }
    }

    public function read($raw = "")
    {
        if ($this->status === "Connected") {                    //Make sure Socket is Connected
            $response = @socket_read($this->sock, 1024);        //Read some bytes

            if ($response === false && !$this->isBusy()) {      //If there was an error & the error is not that it's busy
                $this->status = "Error";
                return false;
            } else if (strlen($response) === 0) {               //If there was no error but also no data
                return $raw;
            }

            if (strlen($response) === 1024) {                   //If the entire buffer was full loop back to see if there is more data
                return $this->read($raw . $response);
            } else {
                return $raw . $response;
            }
        } else {                                                //Not Connected Yet
            return false;
        }
    }

    public function send($msg)
    {
        if ($this->status === "Connected") {
            $bytes = @socket_write($this->sock, $msg);

            if ($bytes === false && !$this->isBusy()) {         //There was an error and it's not busy
                $this->status = "Error";
                return false;
            } else {                                            //The message was sent
                return true;
            }
        } else {                                                //Not Connected Yet
            return false;
        }
    }

    public function hasDataToRead()
    {
        $r = array($this->sock);
        $n = null;

        $event = socket_select($r, $n, $n, 0);                  //Check if socket has data on buffer

        if ($event === false) {                                 //There was an error while checking
            $this->status = "Error";
            return false;
        } else {                                                //If the function checked successfully 
            return (bool)(count($r));                           //Return whether it has data or not
        }
    }

    protected function isBusy()
    {
        $errNum = socket_last_error();
        $busyCodes = array(149, 36, 114, 11, 115, 150, 35);

        if (in_array($errNum, $busyCodes)) {                    //If the Socket was busy return true
            return true;
        } else {                                                //Otherwise return false
            $this->status = "Error";
            return false;
        } 
    }
}
