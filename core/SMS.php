<?php

namespace Core;

use Clickatell\Api\ClickatellHttp as Clickatel;
use \Exception;

/**
* CODES :
* 200 - success (clickatel uses 200 and 201 for success)
* 404 - not found
* 505 - validation error
* 500 - request failure
*/

class SMS {
  protected $SMS;
  protected $to;
  protected $message;
  private $log_path;

  public function set_to($val) {
    $this->to = $val;
  }
  public function set_msg($val) {
    $this->message = $val;
  }

  function __construct($to=null, $msg=null) {
    $secrets_str = file_get_contents(__DIR__ . '/../secrets.json');
		try {
						$secrets_json = json_decode($secrets_str);
		} catch (Exception $e) {
						throw new Error($e);
		}
    // ERROR HANDLING
    if ($secrets_json === NULL) {
      throw new Exception('Error Reading Secrets Files!');
    } elseif (
        !isset($secrets_json->SMS_USERNAME) ||
        !isset($secrets_json->SMS_PASSPHRASE) ||
        !isset($secrets_json->SMS_API_ID)) {
          $error = 'Missing CLickatell USERNAME/PASSSWORD/API ID';
        throw new Exception($error);
    } else {
      $username = (string) $secrets_json->SMS_USERNAME;
      $passphrase = (string) $secrets_json->SMS_PASSPHRASE;
      $api_id = (int) $secrets_json->SMS_API_ID;
    }
    $this->SMS = new Clickatel($username, $passphrase, $api_id);
    $this->to = $to;
    $this->message = $msg;
    $this->log_path = __DIR__ . '/../logs/'; /* CAN BE CHANGED TO ANY DISIRED LOCATION */
  }

  public function sed_sms_message() {
    if (is_array($this->to)) {
      foreach( $this->to as $message) {
        $response = $this->forward_message($message);
      }
    } else {
        $response = $this->forward_message($this->to);
    }
    if (in_array($response, array(200,201))) {
      return $response;
    } else {
      return $response;
    }
  }

  private function forward_message($to) {
    $result = $this->validate_destination($to);
    if ($result === false) {
      if (is_array($to)) {
        $to = implode(',', $to);
      }
      $error = "Cell phone validation failed";
      $this->log_error($error, $to, 505);
    } else {
      $to = $result;
      $result = $this->SMS->sendMessage(array($to), $this->message);
      foreach ($result as $message) {
        $response_id = $message->id;
        $response_error = $message->error;
      }
      if ($response_id === null) {
        $error = "Invalid response id from Clickatell";
        $this->log_error($error, $to, 500);
        return 500;
      } else {
        $error = "Message Successfully Sent! (MSG_ID: $response_id)";
        $this->log_error($error, $to, 200);
        $result = array('response'=>$response_id, 'error'=>$response_error);
      }
    }
    return $result;
  }

  private function validate_destination($number) {
    $cell_number = str_replace(' ', '', $number);
    if (strlen($cell_number) > 9) {
      $zar_landline_codes = array(
        '010','011','012','013','014','016','017','021','022','023','027',
        '028','031','032','033','034','035','036','039','040','041','042',
        '043','044','045','046','047','048','051','053','054','056','057','058'
      ); // telephone codes in ZA
      preg_match('/^[0-5]{2}[0-9]{1}/', $cell_number, $matches, PREG_OFFSET_CAPTURE);
      if (isset($matches[0][0])) {
        if (in_array($matches[0][0], $zar_landline_codes)) {
          return false;
        } // end landline check
      } // end telephone code result check

      $pattern = '/^[+]/';
      $number = (preg_match($pattern, $cell_number) ? preg_replace($pattern, '', $cell_number):$cell_number);
      $pattern = '/^0/';
      $number = (preg_match($pattern, $number) ? preg_replace($pattern, '27', $number):$number);

      $pattern = '/^[0-9]{4}[0-9]{3}[0-9]{4}$/';

      if (preg_match($pattern, $number)) {
        return $number;
      }
    } // end stringlen check

    return false;
  }

  private function log_error($error, $recepients = null, $code=null) {
    $create_log = true;
    if (!file_exists($this->log_path)) {
      $create_log = false;
      if (!mkdir($this->log_path)) {
        throw new Exception("Error On Attempt To Create A Log File Path (404 - Log File Path Not Found)", 1);
      } else {
        $create_log = true;
      }
    }

    if ($create_log) {
      $file = $this->log_path . '/history.log'; // can change this to a suitable name
      $log_message = "- - [" . date('d/M/Y:H:i:s') . "] ";
      $log_message .= "Code: $code.";
      $log_message .= $recepients !== null ? " SMS Destinations: $recepients. SMS Content: $this->message " : " ";
      $log_message .= "Error/Message: $error.";
      // WRITE TO LOG
      $file = file_put_contents($file, $log_message.PHP_EOL, FILE_APPEND | LOCK_EX);
    } else {
      return false;
    }

    return 0;
  }
}
