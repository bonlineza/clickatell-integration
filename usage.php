<?php

// REQUIRE VENDOR/AUTOLOAD TO LOAD CLASSES AND FILES
require_once('vendor/autoload.php');

$SendSms  =  new Core\SMS(array('+1234567890'), 'Your Message Here');

var_dump($SendSms->sed_sms_message());
