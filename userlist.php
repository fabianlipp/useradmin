<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'helpers.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
require_once(BASE_PATH . 'classes/metagroup.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);
$metagroups = Metagroup::readMetagroups($ldapconn);
$groupOus = GroupOu::readGroupOus($ldapconn);

ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>
<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="UserlistController as list">
      <!-- show alerts -->
      <usradm-alert-container alerts="list.alerts">
      </usradm-alert-container>

      <h1>User anzeigen</h1>

      <usradm-userlist-search list="list">
      </usradm-userlist-search>

      <table class="table table-hover sortable">
        <!-- Titelzeile der Tabelle mit Sortiermöglichkeiten -->
        <tr usradm-userlist-header list="list"></tr>

        <!-- Tabelleneintrag für Benutzer -->
        <tr ng-repeat-start="user in list.userData
              | orderBy:list.sortField:list.sortReverse
              | filter:list.searchText"
            ng-if="!user.expanded"
            ng-click="list.expandClick(user)">
          <td>{{user.cn}}</td>
          <td>{{user.displayName}}</td>
          <td>{{user.mail}}</td>
        </tr>

        <!-- Details für Benutzer -->
        <tr ng-repeat-end="" ng-if="user.expanded">
          <td colspan="3">
            <usradm-edit-user user="user"
              closable="true" editable="true"
              expand-click="list.expandClick(user)">
            </usradm-edit-user>
          </td>
        </tr>
      </table>

      <!-- Modal-Dialog zur Gruppenauswahl zum Hinzufügen -->
      <usradm-group-add-modal
          group-data="list.groupEditServ.groupData"
          metagroup-data="list.groupEditServ.metagroupData">
      </usradm-group-add-modal>
    </div>

    <?php echoJsonDataAsScript("jsonUsers", $users); ?>
    <?php echoJsonDataAsScript("jsonMetagroups", $metagroups); ?>
    <?php echoJsonDataAsScript("jsonGroups", $groupOus); ?>

<?php include('html_bottom.inc.php'); ?>
