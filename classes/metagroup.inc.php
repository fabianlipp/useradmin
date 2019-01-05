<?php

require_once(__DIR__ . '/../config.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php'); // TODO: Needed?
require_once(BASE_PATH . 'classes/group.inc.php');

class Metagroup {
  var $dn;
  var $cn;
  var $description;
  var $members;

  private $ldapconn;

  const FILTER_METAGROUPS = "(objectclass=groupOfNames)";

  public static function readMetagroups($ldapconn) {
    if (METAGROUP_DN === false) {
      return array();
    }

    $metagroups = array();
    $search = ldap_list($ldapconn, METAGROUP_DN, Metagroup::FILTER_METAGROUPS,
        array("cn", "description", "member"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        $metagroups[] = Metagroup::readFromLdapEntry($ldapconn, $entry);
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }
    return $metagroups;
  }



  public static function loadMetagroup($ldapconn, $dn) {
    $search = ldap_read($ldapconn, $dn, Metagroup::FILTER_METAGROUPS,
        array("cn", "description", "member"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);

      return Metagroup::readFromLdapEntry($ldapconn, $entry);
    }
  }



  private static function readFromLdapEntry($ldapconn, $entry) {
    $newMetagroup = new Metagroup();
    $newMetagroup->dn = ldap_get_dn($ldapconn, $entry);

    $att = ldap_get_attributes($ldapconn, $entry);
    if (isset($att['cn']) && $att['cn']['count'] == 1) {
      $newMetagroup->cn = $att['cn'][0];
    }
    $vals = ldap_get_values($ldapconn, $entry, "description"); // TODO: Needed? -> also in group.inc.php
    if (isset($att['description']) && $att['description']['count'] == 1) {
      $newMetagroup->description = $att['description'][0];
    }
    if (isset($att['member'])) {
      $newMetagroup->members = [];
      for($i = 0; $i < $att['member']['count']; $i++) {
        $dn = $att['member'][$i];
        $newMetagroup->members[] = $dn;
      }
    } else {
      $newMetagroup->members = [];
    }

    $newMetagroup->ldapconn = $ldapconn;
    return $newMetagroup;
  }



  public function loadUsers() {
    $search = ldap_read($this->ldapconn, $this->dn, Group::FILTER_GROUPS,
        array("member"));
    if (ldap_count_entries($this->ldapconn, $search) > 0) {
      $entry = ldap_first_entry($this->ldapconn, $search);

      $att = ldap_get_attributes($this->ldapconn, $entry);
      if (isset($att['member'])) {
        $this->members = [];
        for($i = 0; $i < $att['member']['count']; $i++) {
          $dn = $att['member'][$i];
          if ($dn != DUMMY_USER_DN) {
            $this->members[] = User::readUser($this->ldapconn, $dn);
          }
        }
      } else {
        $this->members = [];
      }
    }
  }
}

?>
