<?php

require_once('config.inc.php');

class Group {
  var $dn;
  var $cn;
  var $description;

  private $ldapconn;

  const FILTER_GROUPS = "(objectclass=groupOfNames)";

  public static function readGroups($ldapconn, $baseDn) {
    $groups = array();
    $search = ldap_list($ldapconn, $baseDn, Group::FILTER_GROUPS,
        array("cn", "description"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        $newGroup = new Group();
        $newGroup->dn = ldap_get_dn($ldapconn, $entry);

        $att = ldap_get_attributes($ldapconn, $entry);
        if (isset($att['cn']) && $att['cn']['count'] == 1) {
          $newGroup->cn = $att['cn'][0];
        }
        $vals = ldap_get_values($ldapconn, $entry, "description");
        if (isset($att['description']) && $att['description']['count'] == 1) {
          $newGroup->description = $att['description'][0];
        }

        $newGroup->ldapconn = $ldapconn;
        $groups[] = $newGroup;
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }
    return $groups;
  }



  public static function loadGroup($ldapconn, $dn) {
    $search = ldap_read($ldapconn, $dn, Group::FILTER_GROUPS,
        array("cn", "description"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);

      $newGroup = new Group();
      $newGroup->dn = ldap_get_dn($ldapconn, $entry);

      $att = ldap_get_attributes($ldapconn, $entry);
      if (isset($att['cn']) && $att['cn']['count'] == 1) {
        $newGroup->cn = $att['cn'][0];
      }
      $vals = ldap_get_values($ldapconn, $entry, "description");
      if (isset($att['description']) && $att['description']['count'] == 1) {
        $newGroup->description = $att['description'][0];
      }

      $newGroup->ldapconn = $ldapconn;
      return $newGroup;
    }
  }



  public function addUser($dn) {
    $entry = array();
    $entry['member'] = $dn;
    if (ldap_mod_add($this->ldapconn, $this->dn, $entry) === false) {
      return false;
    } else {
      return true;
    }
  }
}

class GroupOu {
  var $dn;
  var $ou;
  var $description;

  var $groups;

  const FILTER_GROUP_OUS = "(objectclass=organizationalUnit)";

  public static function readGroupOus($ldapconn) {
    $ous = array();
    $search = ldap_list($ldapconn, GROUP_DN, GroupOu::FILTER_GROUP_OUS,
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
