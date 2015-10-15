<?php
require_once('config.inc.php');

require_once('ldap.inc.php');
require_once('groupOu.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$ous = GroupOu::readGroupOus($ldapconn);

ldap_close($ldapconn);


?>
<?php include('html_head.inc.php'); ?>

  <body>

<?php include('navigation.inc.php'); ?>

    <div class="container">
      <h1>Gruppen anzeigen</h1>

      <ul class="list-group">
<?php foreach ($ous as $ou) { ?>
        <li class="list-group-item">
          <h4 class="list-group-item-heading">
            <?php echo $ou->ou ?>
            <span class="small">
              (<?php echo $ou->dn ?>)
            </span>
          </h4>
          <p class="list-group-item-text"><?php echo $ou->description ?></p>
<?php     if (!empty($ou->groups)) { ?>
          <ul class="list-group">
<?php       foreach ($ou->groups as $group) { ?>
            <li class="list-group-item">
              <h5 class="list-group-item-heading">
                <?php echo $group->cn ?>
                <span class="small">
                  (<?php echo $group->dn ?>)
                </span>
              </h5>
              <p class="list-group-item-text"><?php echo $group->description ?></p>
            </li>
<?php       } ?>
          </ul>
<?php     } ?>
        </li>
<?php } ?>
      </ul>

      <p><pre><?php print_r($ous); ?></pre></p>
    </div>

<?php include('html_bottom.inc.php'); ?>
