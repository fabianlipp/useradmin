<?php
require_once(__DIR__ . '/../config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
session_start();

$postdata = file_get_contents("php://input");
$request = (array) json_decode($postdata);

if (empty($request['dn'])) {
  http_response_code(400);
  die("Missing parameter: dn");
}

// read user from LDAP
$ldapconn = ldap_bind_session();
$user = User::readUser($ldapconn, $request['dn']);

$retval = array();
if ($user->deleteFromDirectory($ldapconn) === true) {
  // success
  http_response_code(200);
} else {
  http_response_code(500);
  $retval["detail"] = ldap_error($ldapconn);
  $retval["message"] = "Could not write change to LDAP directory";
}

ldap_close($ldapconn);
echo json_encode($retval);

?>
