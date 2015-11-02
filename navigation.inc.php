<?php

require_once('config.inc.php');

function classIfActive($requestUri) {
    $current_file_name = basename($_SERVER['REQUEST_URI']);

    if ($current_file_name == $requestUri)
        echo ' class="active"';
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
        <div id="navbarCollapse" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li<?php classIfActive("index.php"); ?>><a href="index.php">Home</a></li>
            <li<?php classIfActive("gruppen.php"); ?>><a href="gruppen.php">Gruppen</a></li>
            <li<?php classIfActive("userlist.php"); ?>><a href="userlist.php">User</a></li>
            <li<?php classIfActive("changePassword.php"); ?>><a href="changePassword.php">Passwort ändern</a></li>
            <!--
            <li class="dropdown">
              <a data-toggle="dropdown" class="dropdown-toggle" href="#">Messages <b class="caret"></b></a>
              <ul role="menu" class="dropdown-menu">
                <li><a href="#">Inbox</a></li>
                <li><a href="#">Drafts</a></li>
                <li><a href="#">Sent Items</a></li>
                <li class="divider"></li>
                <li><a href="#">Trash</a></li>
              </ul>
            </li>
            -->
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
