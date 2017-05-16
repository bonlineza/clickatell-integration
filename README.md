# clickatell-integration
Clickatell http/s integeration API

## Getting Started
Clone the Repository

```
clone repo or download zip
```

### Prerequisites

You will need the following installed
* phpmyadmin
* Mysql
* PHP [5.5 _or_ < 7]
* Composer

```
Read more on composer (https://getcomposer.org/)
```

### Modify Credential Files...

```
See sample-secrets.json.
```

### Sending A message
```
<?php

require_once(__DIR__ . '/../../vendor/autoload.php');

$SendSms  =  new Lib\SMS(array('+1234567890'), 'This is a test message');

var_dump($SendSms->sed_sms_message());

```
A successful response:
```
{
  ["response"]=> "YOUR_MESSAGE_ID",
  ["error"]=> false
}
```

### logs
On development, we make use of logs to catch any 'silent' errors.
```
File Path: Lib/logs/
```
