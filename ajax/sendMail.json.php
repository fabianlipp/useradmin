<?php
require_once(__DIR__ . '/../config.inc.php');
require_once 'Mail.php';

require_once(BASE_PATH . 'ldap.inc.php');
session_start();
$ldapconn = ldap_bind_session(); // test if user is logged in
ldap_close($ldapconn);

$postdata = file_get_contents("php://input");
$request = (array) json_decode($postdata);
$mailform = $request['mailform'];

$mailer = Mail::factory('mail');
$headers = array(
  'From' => $mailform->sender,
  'To' => $mailform->recipient,
  'Subject' => $mailform->subject);


$retval = array();
if ($mailer->send($mailform->recipient, $headers, $mailform->mailbody)) {
  // success
  http_response_code(200);
} else {
  http_response_code(500);
  $retval["message"] = "Could not send email";
}

echo json_encode($retval);

?>
