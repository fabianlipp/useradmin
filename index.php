<?php
require_once('config.inc.php');
require_once('ldap.inc.php');
session_start();

$ldapconn = ldap_bind_session();

$sr = ldap_search($ldapconn, "dc=dpsg-wuerzburg,dc=de", "(objectclass=*)");
$res_count = ldap_count_entries($ldapconn, $sr);
$entries = ldap_get_entries($ldapconn, $sr);

ldap_close($ldapconn);

?>
<?php include('html_head.inc.php'); ?>

  <body>

    <?php include('navigation.inc.php'); ?>

    <div class="container">
      <h1>My First Bootstrap Page</h1>
      <p>Eingeloggt als <?php echo $_SESSION['ldapDn'] ?>.</p> 

      <p>Ergebnis der Suche: <?php echo($sr); ?></p>
      <p>Anzahl Einträge: <?php echo($res_count); ?></p>
      <p><pre><?php print_r($entries); ?></pre></p>
    </div>

<?php include('html_bottom.inc.php'); ?>
