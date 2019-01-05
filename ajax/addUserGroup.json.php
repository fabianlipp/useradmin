<?php
require_once(__DIR__ . '/../config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
require_once(BASE_PATH . 'classes/metagroup.inc.php');
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
$r_groupdn = $request['groupdn'];

if (!isset($request['isMetagroup'])) {
  http_response_code(400);
  die("Missing parameter: isMetagroup");
}
$isMetagroup = $request['isMetagroup'];

$ldapconn = ldap_bind_session();
$user = User::readUser($ldapconn, $userdn);

$groupDns = null;
if ($isMetagroup) {
  $metagroup = Metagroup::loadMetagroup($ldapconn, $r_groupdn);
  $groupDns = $metagroup->members;
} else {
  $groupDns = array($r_groupdn);
}

$retval = array();
foreach ($groupDns as $groupDn) {
  if (in_array($groupDn, $user->group_dns)) {
    // user is in this group already
    continue;
  }
  $group = Group::loadGroup($ldapconn, $groupDn);
  if ($group->addUser($userdn) !== true) {
    http_response_code(500);
    $retval["detail"] = ldap_error($ldapconn);
    $retval["message"] = "Could not write change to LDAP directory";
    break;
  }
}

if (empty($retval)) {
  // no problems occured
  http_response_code(200);
  $user = User::readUser($ldapconn, $userdn);
  $user->loadGroupInformation();
  $retval["user"] = $user;
}

ldap_close($ldapconn);
echo json_encode($retval);

?>
