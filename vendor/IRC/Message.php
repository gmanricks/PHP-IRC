<?php

namespace IRC;

Class Message
{
    public $owner;
    public $command;
    public $params;

    public function __construct($raw)
    {
        $this->process($raw);
    }

    public function process($raw)
    {
        $owner = "Server";
        if ($raw[0] == ":") {
            list($owner, $command, $params) = explode(" ", $this->escapeIrcStr($raw), 3);
        } else {
            list($command, $params) = explode(" ", $this->escapeIrcStr($raw), 2);
        }

        $this->owner = $owner;
        $this->command = $command;
        $this->params = $params;
    }

    protected function escapeIrcStr($str)
    {
        return str_replace(":", "", $str);
    }
}
