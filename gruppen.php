<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$ous = GroupOu::readGroupOus($ldapconn);

foreach ($ous as $ou) {
  foreach ($ou->groups as $group) {
    $group->loadUsers();
  }
}

ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>

  <body>

<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="GrouplistController as list">
      <h1>Gruppen anzeigen</h1>

      <div id="accordion" class="panel-group">
        <div class="panel panel-default" ng-repeat="ou in list.groupData">
          <div data-toggle="collapse" href="#collapse{{ou.ou}}"
              data-parent="#accordion"
              class="panel-heading clickable">
            <h4 class="panel-title">
              {{ou.ou}}
              <span class="small">
                ({{ou.dn}})
              </span>
            </h4>
            <p class="list-group-item-text">
              {{ou.description}}
            </p>
          </div>
          <div id="collapse{{ou.ou}}" class="panel-collapse collapse">
            <ul class="list-group" ng-if="ou.groups.length">
              <li class="list-group-item clickable"
                  ng-repeat="group in ou.groups">
                <h5 class="list-group-item-heading">
                  {{group.cn}}
                  <span class="small">
                    ({{group.dn}})
                  </span>
                </h5>
                <p class="list-group-item-text">
                  {{group.description}}
                </p>
                <ul ng-if="group.members.length">
                  <li ng-repeat="user in group.members">
                    {{user.dn}}
                  </li>
                </ul>
              </li>
            </ul>
            <div class="panel-body" ng-if="!ou.groups.length">
              Keine Gruppen in dieser Kategorie.
            </div>
          </div> <!-- panel-collapse -->
        </div> <!-- panel -->
      </div> <!-- panel-group -->
    </div>

    <script type="application/json" json-data id="jsonGroupOus">
      <?php echo json_encode($ous, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>

<?php include('html_bottom.inc.php'); ?>
