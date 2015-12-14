<?php
require_once('config.inc.php');
require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
session_start();


if (isset($_POST['signIn'])) {
  // Form was submitted
  $ldapDn = $_POST['inputDn'];
  
  //prefix and suffix can be added in config.inc.php
  if (strpos($ldapDn,"=")===false) $ldapDn = LOGIN_DN_PREFIX.$ldapDn.LOGIN_DN_SUFFIX;
  
  $password = $_POST['inputPassword'];

  // Test bind to LDAP server
  $ldapconn = ldap_connect_options();
  $bind_success = ldap_bind($ldapconn, $ldapDn, $password);
  if ($bind_success) {
    $user = User::readUser($ldapconn, $ldapDn);
    $_SESSION['ldapDn'] = $ldapDn;
    $_SESSION['password'] = $password;
    $_SESSION['displayName'] = $user->displayName;
    session_write_close();
    ldap_close($ldapconn);
    header('Location: index.php');
    exit;
  }
  @ldap_close($ldapconn);
}

?>
<?php include('html_head.inc.php'); ?>
    <div class="container">
      <form class="form-signin" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputDn" class="sr-only">LDAP DN</label>
        <input type="text" id="inputDn" name="inputDn" class="form-control" placeholder="LDAP DN" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Password" required>
        <button type="submit" id="signIn" name="signIn" class="btn btn-lg btn-primary btn-block">Sign in</button>
      </form>
    </div> <!-- /container -->

<?php include('html_bottom.inc.php'); ?>
