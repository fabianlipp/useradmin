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
    <link rel="stylesheet" href="<?php echo LIBS_URL ?>bootstrap/3/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo LIBS_URL ?>bootstrap/3/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?php echo LIBS_URL ?>font-awesome/4/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/useradmin.css">
    <script src="<?php echo LIBS_URL ?>jquery/3.1/js/jquery.min.js"></script>
    <script src="<?php echo LIBS_URL ?>bootstrap/3/js/bootstrap.min.js"></script>
    <script src="<?php echo LIBS_URL ?>markup.js/1.5/js/markup.min.js"></script>
<?php if (defined('USE_ANGULAR')) { ?>
    <script src="<?php echo LIBS_URL ?>angular/1.6/js/angular.min.js"></script>
    <script src="<?php echo LIBS_URL ?>angular-bootstrap/2.3/js/ui-bootstrap-tpls.min.js"></script>
    <script src="js/angular-app.js"></script>
    <script src="<?php echo LIBS_URL ?>angular-animate/1.6/js/angular-animate.min.js"></script>
    <link href="<?php echo LIBS_URL ?>angular-xeditable/0.5/css/xeditable.min.css" rel="stylesheet">
    <script src="<?php echo LIBS_URL ?>angular-xeditable/0.5/js/xeditable.min.js"></script>
<?php } ?>
<?php if (isset($filespecific_js)) { echo $filespecific_js; } ?>
  </head>
  <body>
