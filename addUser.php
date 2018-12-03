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
<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="AddUserController as adduser">
      <!-- show alerts -->
      <usradm-alert-container alerts="adduser.alerts">
      </usradm-alert-container>

      <h1>User anlegen</h1>

      <!-- Step 1: User anlegen -->
      <div class="ngStepAnimated" id="step1"
          ng-if="adduser.step === 1 && !adduser.emailStepActive"
          ng-class="{'moveToRight' : adduser.moveToRight}">
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
            <label class="control-label col-sm-2" for="username">Username:</label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="text" class="form-control" id="username"
                    ng-model="adduser.userform.cn" />
                <span class="input-group-btn">
                  <button class="btn btn-primary" type="button"
                      ng-click="adduser.suggestUsername()">
                    Username vorschlagen
                  </button>
                </span>
              </div>
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
              <button class="btn btn-primary"
                  ng-click="adduser.completeStep1()">
                User anlegen
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Step 2: Gruppen zuordnen -->
      <div class="ngStepAnimated" id="step2"
          ng-if="adduser.user && adduser.step === 2 && !adduser.emailStepActive"
          ng-class="{'moveToRight' : adduser.moveToRight}">
        <usradm-edit-user user="adduser.user" editable="true">
        </usradm-edit-user>
        <button class="btn btn-primary pull-right"
            ng-click="adduser.completeStep2()">
          Weiter
        </button>
      </div>

      <!-- Modal-Dialog zur Gruppenauswahl zum Hinzufügen -->
      <usradm-group-add-modal
          group-data="adduser.groupEditServ.groupData">
      </usradm-group-add-modal>

      <usradm-send-email
        user="adduser.user"
        userpassword="adduser.userpassword"
        email-step-active="adduser.emailStepActive"
        move-to-right="adduser.moveToRight">
      </usradm-send-email>
    </div>

    <!-- data for the group list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonGroups">
      <?php echo json_encode($groupOus, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>
    <script type="application/json" json-data id="mailSettings">
<?php
      $mail_templates = array();
      foreach (MAIL_TEMPLATES['addUser'] as $template) {
        $mail_templates[] = array(
          'name' => (isset($template['name'])?$template['name']:basename($template['file'])),
          'subject' => $template['subject'],
          'template' => file_get_contents(BASE_PATH . $template['file']));
      }
      $mailSettings = array(
        'sender' => MAIL_SENDER,
        'sendername' => $_SESSION['givenName'],
        'templates' => $mail_templates);
      echo json_encode($mailSettings, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT);
?>
    </script>


<?php include('html_bottom.inc.php'); ?>
