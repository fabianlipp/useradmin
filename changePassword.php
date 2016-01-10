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
      <div id="pwChangeModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <form class="form-horizontal" role="form">
              <div class="modal-header">
                <button type="button" class="close"
                    data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Passwort ändern</h4>
              </div>
              <div class="modal-body">
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
              </div> <!-- modal-body -->
              <div class="modal-footer">
                <input type="submit"
                    class="btn btn-primary pull-right btn-sm rbtnMargin"
                    ng-click="list.changePassword()"
                    ng-disabled="!list.pwd1 || !list.isSamePw()"
                    value="Ändern" />
                <button type="button"
                    class="btn pull-right btn-sm"
                    data-dismiss="modal">
                  Abbrechen
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

<?php include('html_bottom.inc.php'); ?>
