<?php

function ldap_connect_options() {
  $ldapconn = ldap_connect(LDAP_SERVER)
    or die('Cannot connect to LDAP server ' . LDAP_SERVER . '.');
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
  return $ldapconn;
}

function ldap_bind_session() {
  if (!isset($_SESSION['ldapDn']) || !$_SESSION['ldapDn']) {
    header('Location: login.php');
    exit;
  }

  $ldapDn = $_SESSION['ldapDn'];
  $password = $_SESSION['password'];
  $ldapconn = ldap_connect_options();
  $bind_success = ldap_bind($ldapconn, $ldapDn, $password);
  if (!$bind_success) {
    unset($_SESSION['ldapDn']);
    unset($_SESSION['password']);
    header('Location: login.php');
    exit;
  }
  return $ldapconn;
}

