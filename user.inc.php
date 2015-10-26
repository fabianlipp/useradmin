<?php

require_once('config.inc.php');
require_once('groupOu.inc.php');

class User {
  var $dn;
  var $cn;
  var $mail;
  var $displayName;
  private $group_dns;
  var $groups = null;

  private $ldapconn;

  const FILTER_USERS = "(objectclass=inetOrgPerson)";

  public static function readUsers($ldapconn) {
    $users = array();
    $search = ldap_list($ldapconn, USER_DN, User::FILTER_USERS,
        array("cn", "mail", "displayName", "memberOf"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        // Generate object and store dn
        $newUser = new User();
        $newUser->dn = ldap_get_dn($ldapconn, $entry);

        // Load attributes
        $att = ldap_get_attributes($ldapconn, $entry);

        if (isset($att['cn']) && $att['cn']['count'] == 1) {
          $newUser->cn = $att['cn'][0];
        }

        if (isset($att['mail']) && $att['mail']['count'] == 1) {
          $newUser->mail = $att['mail'][0];
        }

        if (isset($att['displayName']) && $att['displayName']['count'] == 1) {
          $newUser->displayName = $att['displayName'][0];
        }

        $groups = [];
        if (isset($att['memberOf'])) {
          for ($i = 0; $i < $att['memberOf']['count']; $i++) {
            $groups[] = $att['memberOf'][$i];
          }
        }
        $newUser->group_dns = $groups;

        // Store user into array
        $newUser->ldapconn = $ldapconn;
        $users[] = $newUser;
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }
    return $users;
  }



  public static function readUser($ldapconn, $dn) {
    $search = ldap_read($ldapconn, $dn, USER::FILTER_USERS,
        array("cn", "mail", "displayName", "memberOf"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);

      $newUser = new User();
      $newUser->dn = ldap_get_dn($ldapconn, $entry);

      // Load attributes
      $att = ldap_get_attributes($ldapconn, $entry);

      if (isset($att['cn']) && $att['cn']['count'] == 1) {
        $newUser->cn = $att['cn'][0];
      }

      if (isset($att['mail']) && $att['mail']['count'] == 1) {
        $newUser->mail = $att['mail'][0];
      }

      if (isset($att['displayName']) && $att['displayName']['count'] == 1) {
        $newUser->displayName = $att['displayName'][0];
      }

      $groups = [];
      if (isset($att['memberOf'])) {
        for ($i = 0; $i < $att['memberOf']['count']; $i++) {
          $groups[] = $att['memberOf'][$i];
        }
      }
      $newUser->group_dns = $groups;

      $newUser->ldapconn = $ldapconn;
      return $newUser;
    }
  }



  public function loadGroupInformation() {
    $this->groups = array();
    foreach ($this->group_dns as $dn) {
      $this->groups[] = Group::loadGroup($this->ldapconn, $dn);
    }
  }



  public function changeMail($newMail) {
    $entry = array();
    $entry["mail"] = $newMail;
    if (ldap_modify($this->ldapconn, $this->dn, $entry) === false) {
      return false;
    } else {
      $this->mail = $newMail;
      return true;
    }
  }



  public function changeDisplayName($newName) {
    $entry = array();
    $entry["displayName"] = $newName;
    if (ldap_modify($this->ldapconn, $this->dn, $entry) === false) {
      return false;
    } else {
      $this->displayName = $newName;
      return true;
    }
  }
}


?>
