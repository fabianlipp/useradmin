<?php
require_once('config.inc.php');
session_start();

// Session löschen
$_SESSION = array();
session_destroy();

header('Location: login.php');
exit;

?>
