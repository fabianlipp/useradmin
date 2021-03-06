<?php
require_once(__DIR__ . '/../config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$postdata = file_get_contents("php://input");
$request = (array) json_decode($postdata);

if (empty($request['userdn'])) {
  http_response_code(400);
  die("Missing parameter: userdn");
}
$userdn = $request['userdn'];

if (empty($request['groupdn'])) {
  http_response_code(400);
  die("Missing parameter: groupdn");
}
$groupdn = $request['groupdn'];

// read group from LDAP
$ldapconn = ldap_bind_session();
$group = Group::loadGroup($ldapconn, $groupdn);

$retval = array();

if ($group->removeUser($userdn) === true) {
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
