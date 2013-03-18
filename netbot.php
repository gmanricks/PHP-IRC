<?php

require "vendor/autoload.php";

echo "Connecting: \n";
$bot = new \IRC\IRC("irc.freenode.net");
sleep(5);
var_dump($bot->pollForData());
sleep(1);


echo "Loging In: \n";
$bot->login("bobisatestbot");
sleep(3);
var_dump($bot->pollForData());
sleep(1);

echo "Joining Room: \n";
$bot->join("nettuts");
sleep(3);
var_dump($bot->pollForData());
sleep(1);

echo "Posting Message: \n";
$bot->message("gmanricks", "test 2");
var_dump($bot->pollForData());
sleep(1);