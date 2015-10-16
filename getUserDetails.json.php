<?php
require_once('config.inc.php');

require_once('ldap.inc.php');
require_once('user.inc.php');
require_once('groupOu.inc.php');
session_start();

if (empty($_GET['dn'])) {
  http_response_code(400);
  die("Missing parameter: dn");
}
$dn = $_GET['dn'];

$ldapconn = ldap_bind_session();
$user = User::readUser($ldapconn, $dn);
$user->loadGroupInformation($ldapconn);
ldap_close($ldapconn);

echo json_encode($user);
?>
