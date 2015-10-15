<?php

require_once('config.inc.php');

class Group {
  var $dn;
  var $cn;
  var $description;

  public static function readGroups($ldapconn, $baseDn) {
    $filter_groups = "(objectclass=groupOfNames)";

    $groups = array();
    $search = ldap_list($ldapconn, $baseDn, $filter_groups,
        array("cn", "description"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        $newGroup = new Group();
        $newGroup->dn = ldap_get_dn($ldapconn, $entry);
        $vals = ldap_get_values($ldapconn, $entry, "cn");
        if ($vals['count'] == 1) {
          $newGroup->cn = $vals[0];
        }
        $vals = ldap_get_values($ldapconn, $entry, "description");
        if ($vals['count'] == 1) {
          $newGroup->description = $vals[0];
        }

        $groups[] = $newGroup;
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }
    return $groups;
  }
}

class GroupOu {
  var $dn;
  var $ou;
  var $description;

  var $groups;

  public static function readGroupOus($ldapconn) {
    $filter_ou = "(objectclass=organizationalUnit)";

    $ous = array();
    $search = ldap_list($ldapconn, GROUP_DN, $filter_ou,
        array("ou", "description"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        $newOu = new GroupOu();
        $newOu->dn = ldap_get_dn($ldapconn, $entry);
        $vals = ldap_get_values($ldapconn, $entry, "ou");
        if ($vals['count'] == 1) {
          $newOu->ou = $vals[0];
        }
        $vals = ldap_get_values($ldapconn, $entry, "description");
        if ($vals['count'] == 1) {
          $newOu->description = $vals[0];
        }
        $newOu->groups = Group::readGroups($ldapconn, $newOu->dn);

        $ous[] = $newOu;
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }

    return $ous;
  }
}





?>
