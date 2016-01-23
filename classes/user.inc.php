<?php

require_once(__DIR__ . '/../config.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');

class User {
  var $dn;
  var $cn;
  var $mail;
  var $displayName;
  var $sn;
  var $givenName;
  private $group_dns;
  var $groups = null;

  private $ldapconn;

  const FILTER_USERS = "(objectclass=inetOrgPerson)";

  public static function readUsers($ldapconn) {
    $users = array();
    $search = ldap_list($ldapconn, USER_DN, User::FILTER_USERS,
        array("cn", "mail", "displayName", "sn", "givenName", "memberOf"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);
      do {
        $users[] = User::readFromLdapEntry($ldapconn, $entry);
      } while ($entry = ldap_next_entry($ldapconn, $entry));
    }
    return $users;
  }



  public static function readUser($ldapconn, $dn) {
    $search = ldap_read($ldapconn, $dn, USER::FILTER_USERS,
        array("cn", "mail", "displayName", "sn", "givenName", "memberOf"));
    if (ldap_count_entries($ldapconn, $search) > 0) {
      $entry = ldap_first_entry($ldapconn, $search);

      return User::readFromLdapEntry($ldapconn, $entry);
    }
  }



  private static function readFromLdapEntry($ldapconn, $entry) {
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

    if (isset($att['sn']) && $att['sn']['count'] == 1) {
      $newUser->sn = $att['sn'][0];
    }

    if (isset($att['givenName']) && $att['givenName']['count'] == 1) {
      $newUser->givenName = $att['givenName'][0];
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



  public function loadGroupInformation() {
    $this->groups = array();
    foreach ($this->group_dns as $dn) {
      $this->groups[] = Group::loadGroup($this->ldapconn, $dn);
    }
  }



  public function changeField($field, $newValue) {
    $entry = array();
    $entry[$field] = $newValue;
    if (ldap_modify($this->ldapconn, $this->dn, $entry) === false) {
      return false;
    } else {
      $this->$field = $newValue;
      return true;
    }
  }



  public function changePassword($newPassword) {
    $salt = openssl_random_pseudo_bytes(12);
    $encoded_newPassword = "{SSHA}"
        . base64_encode(hash('sha1', $newPassword . $salt, true)
        . $salt);
    $entry = array();
    $entry["userPassword"] = $encoded_newPassword;
    if (ldap_modify($this->ldapconn, $this->dn, $entry) === false) {
      return false;
    } else {
      return true;
    }
  }



  public static function generateRandomPassword() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz'
      . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    for ($i = 0; $i < 15; $i++) {
            $randstring .= $characters[rand(0, strlen($characters))];
    }
    return $randstring;
  }



  public function addToDirectory($ldapconn) {
    $this->ldapconn = $ldapconn;
    $entry = array();
    $entry["cn"] = $this->cn;
    $entry["mail"] = $this->mail;
    $entry["sn"] = $this->sn;
    $entry["givenName"] = $this->givenName;
    $entry["displayName"] = $this->displayName;
    $entry["objectClass"] = "inetOrgPerson";
    if (ldap_add($this->ldapconn, $this->dn, $entry) === false) {
      return false;
    } else {
      return true;
    }
  }



  public function deleteFromDirectory($ldapconn) {
    if (ldap_delete($this->ldapconn, $this->dn) === false) {
      return false;
    } else {
      return true;
    }
  }
}


?>
