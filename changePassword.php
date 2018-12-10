<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);

ldap_close($ldapconn);

define('USE_ANGULAR', true);
$filespecific_js = <<<EOT
<script type="text/javascript">
//<![CDATA[
  $(window).load(function(){
    $('#pwChangeModal').on('shown.bs.modal', function () {
      $('#pwd1').focus();
    });
  });
//]]>
</script>
EOT;

?>
<?php include('html_head.inc.php'); ?>
<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="UserlistController as list">
      <!-- show alerts -->
      <usradm-alert-container alerts="list.alerts">
      </usradm-alert-container>

      <h1>Passwort ändern</h1>

      <usradm-userlist-search list="list">
      </usradm-userlist-search>

      <table class="table table-hover sortable">
        <!-- Titelzeile der Tabelle mit Sortiermöglichkeiten -->
        <tr usradm-userlist-header list="list"></tr>

        <!-- Tabelleneintrag für Benutzer -->
        <tr ng-repeat="user in list.userData
              | orderBy:list.sortField:list.sortReverse
              | filter:list.searchText"
            ng-click="list.showChangePw(user)">
          <td>
            {{user.cn}}
            <span class="fa fa-refresh"
                ng-show="user.pwChanging"
                ng-class="{'fa-spin' : user.pwChanging}"></span>
            <span class="fa fa-check"
                ng-show="user.pwChanged"></span>
          </td>
          <td>{{user.displayName}}</td>
          <td>{{user.mail}}</td>
        </tr>
      </table>

      <!-- Modal-Dialog zum Passwort ändern -->
      <div id="pwChangeModal"
          class="modal fade"
          role="dialog"
          ng-controller="ChangePasswordController as changePassword">
        <div class="modal-dialog">
          <div class="modal-content">
            <form class="form-horizontal" role="form">
              <div class="modal-header">
                <button type="button" class="close"
                    data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Passwort ändern</h4>
              </div>
              <div class="modal-body">

                <!-- Step 0: set password -->
                <div class="ngStepAnimated" id="step0"
                    ng-if="changePassword.step === 0"
                    ng-class="{'moveToRight' : changePassword.moveToRight}">
                  <div class="form-group">
                    <label class="control-label col-sm-4">
                      User:
                    </label>
                    <div class="col-sm-8 form-control-static">
                      {{list.pwChangeUser.cn}}
                    </div>
                  </div>
                  <div class="form-group has-feedback"
                      ng-class="{'has-warning': !list.pwd1}">
                    <label class="control-label col-sm-4" for="pwd1">
                      Passwort:
                    </label>
                    <div class="col-sm-8">
                      <input type="password" class="form-control"
                          id="pwd1" ng-model="list.pwd1"/>
                      <span class="glyphicon form-control-feedback"
                          ng-show="!list.pwd1"
                          ng-class="{'glyphicon-remove': !list.pwd1}">
                      </span>
                    </div>
                  </div>
                  <div class="form-group has-feedback"
                      ng-class="{'has-success': list.pwd1 && list.isSamePw(),
                          'has-error': list.pwd1 && !list.isSamePw()}">
                    <label class="control-label col-sm-4" for="pwd2">
                      Passwort wiederholen:
                    </label>
                    <div class="col-sm-8">
                      <input type="password" class="form-control"
                          id="pwd2" ng-model="list.pwd2" />
                      <span class="glyphicon form-control-feedback"
                          ng-show="list.pwd1"
                          ng-class="{'glyphicon-ok': list.isSamePw(),
                              'glyphicon-remove': list.pwd1 && !list.isSamePw()}">
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Step 1: choose template -->
                <div class="ngStepAnimated" id="step1"
                    ng-if="changePassword.step === 1"
                    ng-class="{'moveToRight' : changePassword.moveToRight}">
                  <form class="form-horizontal"
                      role="form">
                    <div class="form-group">
                      <label class="control-label col-sm-2">E-Mail-Vorlage:</label>
                      <div class="col-sm-10">
                        <div ng-repeat="(index, template) in changePassword.mailSettings.templates"
                          class="radio">
                          <label>
                            <input type="radio"
                                value="{{index}}"
                                ng-model="changePassword.mailtemplate"
                                name="mailtemplate">
                              {{template.name}}
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button class="btn btn-primary"
                            ng-click="changePassword.stepBack()">
                          Abbrechen
                        </button>
                        <button class="btn btn-primary"
                            ng-click="changePassword.completeStep1()">
                          Weiter
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Step 2: send mail -->
                <div class="ngStepAnimated" id="step2"
                    ng-if="changePassword.step === 2"
                    ng-class="{'moveToRight' : changePassword.moveToRight}">
                  <form class="form-horizontal" role="form">
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="sender">Absender:</label>
                      <div class="col-sm-10">
                        <input type="email"
                            class="form-control"
                            id="sender"
                            ng-model="changePassword.mailform.sender" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="recipient">Empfänger:</label>
                      <div class="col-sm-10">
                        <input type="email"
                            class="form-control"
                            id="recipient"
                            ng-model="changePassword.mailform.recipient" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="subject">Betreff:</label>
                      <div class="col-sm-10">
                        <input type="text"
                            class="form-control"
                            id="subject"
                            ng-model="changePassword.mailform.subject" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-2" for="mailbody">Text:</label>
                      <div class="col-sm-10">
                        <textarea class="form-control" id="mailbody" rows="20"
                            ng-model="changePassword.mailform.mailbody"></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                        <button class="btn btn-primary"
                            ng-click="changePassword.stepBack()">
                          Zurück
                        </button>
                        <button class="btn btn-primary"
                            ng-click="changePassword.sendMail()"
                            ng-disabled="changePassword.mailsending || changePassword.mailsuccess">
                          Mail absenden
                        </button>
                        <span class="fa fa-refresh"
                            ng-show="changePassword.mailsending"
                            ng-class="{'fa-spin' : changePassword.mailsending}">
                        </span>
                        <span class="fa fa-check"
                            ng-show="changePassword.mailsuccess"></span>
                        <span class="fa fa-times"
                            ng-show="changePassword.mailfailure"></span>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Step 3: ready -->
                <div class="ngStepAnimated" id="step3"
                    ng-if="changePassword.step === 3"
                    ng-class="{'moveToRight' : changePassword.moveToRight}">
                  <div class="alert alert-success">Mail erfolgreich versandt</div>
                </div>

              </div> <!-- modal-body -->
              <div class="modal-footer">
                <button type="button"
                    class="btn btn-info pull-left btn-sm"
                    ng-show="changePassword.step === 0"
                    ng-click="changePassword.setRandomPassword(list.pwChangeUser)">
                  Zufällig Generieren &amp; Mailen
                </button>
                <input type="submit"
                    class="btn btn-primary pull-right btn-sm rbtnMargin"
                    ng-show="changePassword.step === 0"
                    ng-click="list.changePassword()"
                    ng-disabled="!list.pwd1 || !list.isSamePw()"
                    value="Ändern" />
                <button type="button"
                    class="btn pull-right btn-sm"
                    ng-show="changePassword.step === 0"
                    data-dismiss="modal">
                  Abbrechen
                </button>
                <button type="button"
                    class="btn pull-right btn-sm"
                    ng-show="changePassword.step !== 0"
                    ng-click="changePassword.reset()"
                    data-dismiss="modal">
                  Fertig
                </button>
              </div> <!-- modal-footer -->
            </form>
          </div> <!-- modal-content -->
        </div> <!-- modal-dialog -->
      </div> <!-- modal -->

    </div> <!-- container -->

    <!-- data for the user list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonUsers">
      <?php echo json_encode($users, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>
	<script type="application/json" json-data id="mailSettings">
<?php
      $mail_templates = array();
      foreach (MAIL_TEMPLATES['changePassword'] as $template) {
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
