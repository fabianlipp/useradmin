<?php
require_once('config.inc.php');
?>
<!DOCTYPE html>
<?php if (defined('USE_ANGULAR')) { ?>
<html lang="en" ng-app="useradminApp">
<?php } else { ?>
<html lang="en">
<?php } ?>
  <head>
  <title><?php echo PAGETITLE;?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/useradmin.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
<?php if (defined('USE_ANGULAR')) { ?>
    <script src="js/angular.min.js"></script>
    <script src="js/ui-bootstrap-tpls.min.js"></script>
    <script src="js/angular-app.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.4.0-rc.2/angular-animate.js"></script>
    <link href="css/xeditable.css" rel="stylesheet">
    <script src="js/xeditable.min.js"></script>
<?php } ?>
  </head>
