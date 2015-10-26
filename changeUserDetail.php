<?php
require_once('config.inc.php');

require_once('ldap.inc.php');
require_once('user.inc.php');
require_once('groupOu.inc.php');
session_start();

$postdata = file_get_contents("php://input");
$request = (array) json_decode($postdata);

if (empty($request['dn'])) {
  http_response_code(400);
  die("Missing parameter: dn");
}
$dn = $request['dn'];

// read user from LDAP
$ldapconn = ldap_bind_session();
$user = User::readUser($ldapconn, $dn);

$retval = array();

// check which field should be changed
if (!empty($request['newMail'])) {
  $newMail = $request['newMail'];
  if ($user->changeMail($newMail) === true) {
    // success
    http_response_code(200);
  } else {
    http_response_code(500);
    $retval["message"] = "Could not write change to LDAP directory";
  }
} elseif (!empty($request['newDisplayName'])) {
  $newName = $request['newDisplayName'];
  if ($user->changeDisplayName($newName) === true) {
    // success
    http_response_code(200);
  } else {
    http_response_code(500);
    $retval["message"] = "Could not write change to LDAP directory";
  }
} else {
  http_response_code(400);
  $retval["message"] = "Got no parameter to change";
}

$retval["mail"] = $user->mail;
$retval["displayName"] = $user->displayName;

ldap_close($ldapconn);
echo json_encode($retval);

?>
