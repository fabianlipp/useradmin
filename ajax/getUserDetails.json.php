<?php
require_once(__DIR__ . '/../config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

if (empty($_GET['dn'])) {
  http_response_code(400);
  die("Missing parameter: dn");
}
$dn = $_GET['dn'];

$ldapconn = ldap_bind_session();
$user = User::readUser($ldapconn, $dn);
$user->loadGroupInformation();
ldap_close($ldapconn);

echo json_encode($user);
?>
