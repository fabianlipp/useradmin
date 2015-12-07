<?php
require_once(__DIR__ . '/../config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ret = '';

$postdata = file_get_contents("php://input");
$request = (array) json_decode($postdata);
if (empty($request['cn'])) {
  http_response_code(400);
  die("Missing parameter: cn");
}
$ldapDn = 'cn=' . $request['cn'] . ',' . USER_DN;
if (empty($request['pw'])) {
  http_response_code(400);
  die("Missing parameter: pw");
}
$password = $request['pw'];

$ret .= $ldapDn . "\n" . $password . "\n";

$ldapconn = ldap_connect_options();
$bind_success = ldap_bind($ldapconn, $ldapDn, $password);
if ($bind_success) {
  $user = User::readUser($ldapconn, $ldapDn);
  $user->loadGroupInformation();
  $_SESSION['ldapDn'] = $ldapDn;
  $_SESSION['password'] = $password;
  $_SESSION['displayName'] = $user->displayName;
  session_write_close();
  ldap_close($ldapconn);
  http_response_code(200);
  echo json_encode($user);
} else {
  http_response_code(403);
  ldap_close($ldapconn);
  echo $ret;
}
?>
