<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$groupOus = GroupOu::readGroupOus($ldapconn);
ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>

  <body>

<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="AddUserController as adduser">
      <!-- show alerts -->
      <div id="alert-container" class="container">
        <div class="col-xs-3"></div>
        <div class="col-xs-6">
          <uib-alert ng-repeat="alert in adduser.alerts.alertList"
              type="{{alert.type}}"
              close="alert.close()"
              dismiss-on-timeout="{{alert.dismiss}}">
            {{alert.msg}}
          </uib-alert>
        </div>
        <div class="col-xs-3"></div>
      </div>

      <h1>User anlegen</h1>

      <div ng-show="adduser.step === 1">
        <form class="form-horizontal" role="form">
          <div class="form-group">
            <label class="control-label col-sm-2" for="pwd">Vorname:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="givenName"
                  ng-model="adduser.userform.givenName" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="pwd">Nachname:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="sn"
                  ng-model="adduser.userform.sn" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="email">Username:</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="username"
                  ng-model="adduser.userform.cn" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="email">E-Mail:</label>
            <div class="col-sm-10">
              <input type="email" class="form-control" id="mail"
                  ng-model="adduser.userform.mail" />
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button class="btn btn-default"
                  ng-click="adduser.completeStep1()">
                User anlegen
              </button>
            </div>
          </div>
        </form>
      </div>

      <div ng-show="adduser.step === 2" ng-if="adduser.user">
        <usradm-edit-user user="adduser.user">
        </usradm-edit-user>
      </div>

      <!-- Modal-Dialog zur Gruppenauswahl zum Hinzufügen -->
      <usradm-group-add-modal
          group-data="adduser.groupEditServ.groupData">
      </usradm-group-add-modal>
    </div>

    <!-- data for the group list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonGroups">
      <?php echo json_encode($groupOus, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>

<?php include('html_bottom.inc.php'); ?>
