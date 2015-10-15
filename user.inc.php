<?php

require_once('config.inc.php');

class User {
  var $dn;
  var $cn;
  var $mail;
  var $displayName;
  var $groups;

  public static function readUsers($ldapconn) {
    $filter_users = "(objectclass=inetOrgPerson)";

    $users = array();
    $search = ldap_list($ldapconn, USER_DN, $filter_users,
        array("cn", "mail", "displayName", "memberOf"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        $newUser = new User();
        $newUser->dn = ldap_get_dn($ldapconn, $entry);
        $vals = ldap_get_values($ldapconn, $entry, "cn");
        if ($vals['count'] == 1) {
          $newUser->cn = $vals[0];
        }
        $vals = ldap_get_values($ldapconn, $entry, "mail");
        if ($vals['count'] == 1) {
          $newUser->mail = $vals[0];
        }
        $vals = ldap_get_values($ldapconn, $entry, "displayName");
        if ($vals['count'] == 1) {
          $newUser->displayName = $vals[0];
        }
        $vals = ldap_get_values($ldapconn, $entry, "memberOf");
        $groups = [];
        for ($i = 0; $i < $vals['count']; $i++) {
          $groups[] = $vals[$i];
        }
        $newUser->groups = $groups;

        $users[] = $newUser;
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }
    return $users;
  }

}


?>
