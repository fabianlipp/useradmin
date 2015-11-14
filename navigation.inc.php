<?php

require_once('config.inc.php');

function classIfActive($requestUri) {
    $current_file_name = basename($_SERVER['REQUEST_URI']);

    if (!is_array($requestUri)) {
      $requestUri = array($requestUri);
    }

    if (in_array($current_file_name, $requestUri)) {
      echo 'active';
    }
}

?>
    <nav role="navigation" class="navbar navbar-inverse navbar-fixed-top">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="container">
        <div class="navbar-header">
          <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a href="#" class="navbar-brand active"><?php echo PAGETITLE; ?></a>
        </div>
        <!-- Collection of nav links, forms, and other content for toggling -->
        <div id="navbarCollapse" class="collapse navbar-collapse <?php classIfActive("index.php"); ?>">
          <ul class="nav navbar-nav">
            <li class="<?php classIfActive("index.php"); ?>"><a href="index.php">Home</a></li>
            <li class="<?php classIfActive("gruppen.php"); ?>"><a href="gruppen.php">Gruppen</a></li>
            <li class="dropdown <?php classIfActive(array("userlist.php", "changePassword.php")); ?>">
              <a data-toggle="dropdown" class="dropdown-toggle" href="#">User<b class="caret"></b></a>
              <ul role="menu" class="dropdown-menu">
                <li class="<?php classIfActive("userlist.php"); ?>"><a href="userlist.php">User bearbeiten</a></li>
                <li class="<?php classIfActive("changePassword.php"); ?>"><a href="changePassword.php">Passwort ändern</a></li>
                <!--<li class="divider"></li>-->
              </ul>
            </li>
          </ul>
          <!--
          <form role="search" class="navbar-form navbar-left">
            <div class="form-group">
              <input type="text" placeholder="Search" class="form-control">
            </div>
          </form>
          -->
          <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php">Logout</a></li>
          </ul>
          <p class="navbar-text navbar-right"><?php echo $_SESSION['ldapDn']; ?></p>
        </div>
      </div>
    </nav>
