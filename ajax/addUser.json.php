<?php
require_once(__DIR__ . '/../config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$postdata = file_get_contents("php://input");
$request = (array) json_decode($postdata);

$params = array('cn', 'mail', 'sn', 'givenName');
foreach ($params as $par) {
  if (empty($request[$par])) {
    http_response_code(400);
    die("Missing parameter: " . $par);
  }
  $$par = $request[$par];
}

$newuser = new User();
$newuser->cn = $cn;
$newuser->mail = $mail;
$newuser->sn = $sn;
$newuser->givenName = $givenName;
$newuser->displayName = $givenName . ' ' . $sn;
$newuser->dn = 'cn=' . $cn . ',' . USER_DN;

// store to LDAP
$ldapconn = ldap_bind_session();

$retval = array();
if ($newuser->addToDirectory($ldapconn) === true) {
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
