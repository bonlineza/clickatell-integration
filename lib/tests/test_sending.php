<?php

require_once(__DIR__ . '/../../vendor/autoload.php');

$new  =  new Lib\SMS(array('+27780281919'), 'Last Test');

var_dump($new->sed_sms_message());

// var_dump($clickatell);
