<?php
require_once('config.inc.php');
require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);
$groupOus = GroupOu::readGroupOus($ldapconn);

ldap_close($ldapconn);

$groupCount = array_sum(array_map(function($groupOu) { return count($groupOu->groups); }, $groupOus));

?>
<?php include('html_head.inc.php'); ?>
<?php include('navigation.inc.php'); ?>

    <div class="container">
      <h1>Startseite</h1>
      <p>Anzahl User: <?php echo(count($users)); ?></p>
      <p>Anzahl Gruppen: <?php echo($groupCount); ?></p>
    </div>

<?php include('html_bottom.inc.php'); ?>
