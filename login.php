<?php
require_once('config.inc.php');
require_once(BASE_PATH . 'ldap.inc.php');
session_start();


if (isset($_POST['signIn'])) {
  // Form was submitted
  $ldapDn = $_POST['inputDn'];
  $password = $_POST['inputPassword'];

  // Test bind to LDAP server
  $ldapconn = ldap_connect_options();
  $bind_success = ldap_bind($ldapconn, $ldapDn, $password);
  ldap_close($ldapconn);
  if ($bind_success) {
    $_SESSION['ldapDn'] = $ldapDn;
    $_SESSION['password'] = $password;
    session_write_close();
    header('Location: index.php');
    exit;
  }
}

?>
<?php include('html_head.inc.php'); ?>

  <body>
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
