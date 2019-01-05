<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'helpers.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);

ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>
<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="UserlistController as list">
      <!-- show alerts -->
      <usradm-alert-container alerts="list.alerts">
      </usradm-alert-container>

      <h1>User löschen</h1>

      <usradm-userlist-search list="list">
      </usradm-userlist-search>

      <table class="table table-hover sortable">
        <!-- Titelzeile der Tabelle mit Sortiermöglichkeiten -->
        <tr usradm-userlist-header list="list"></tr>

        <!-- Tabelleneintrag für Benutzer -->
        <tr ng-repeat="user in list.userData
              | orderBy:list.sortField:list.sortReverse
              | filter:list.searchText"
            ng-click="list.showDeleteUser(user)">
          <td>
            {{user.cn}}
            <span class="fa fa-refresh"
                ng-show="user.userDeleting"
                ng-class="{'fa-spin' : user.userDeleting}"></span>
          </td>
          <td>{{user.displayName}}</td>
          <td>{{user.mail}}</td>
        </tr>
      </table>

      <!-- Modal-Dialog zum Passwort ändern -->
      <div id="userDeleteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <form class="form-horizontal" role="form">
              <div class="modal-header">
                <button type="button" class="close"
                    data-dismiss="modal">&times;</button>
                <h4 class="modal-title">User löschen</h4>
              </div>
              <div class="modal-body">
                <usradm-edit-user user="list.deleteSelectedUser"
                  closable="false" editable="false">
                </usradm-edit-user>
              </div> <!-- modal-body -->
              <div class="modal-footer">
                <input type="submit"
                    class="btn btn-danger pull-right btn-sm rbtnMargin"
                    ng-click="list.deleteUser()"
                    value="Löschen" />
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

    <?php echoJsonDataAsScript("jsonUsers", $users); ?>

<?php include('html_bottom.inc.php'); ?>
