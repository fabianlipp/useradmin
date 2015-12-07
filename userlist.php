<?php
require_once('config.inc.php');

require_once(BASE_PATH . 'ldap.inc.php');
require_once(BASE_PATH . 'classes/user.inc.php');
require_once(BASE_PATH . 'classes/group.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);
$groupOus = GroupOu::readGroupOus($ldapconn);

ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>
<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="UserlistController as list">
      <!-- show alerts -->
      <div id="alert-container" class="container">
        <div class="col-xs-3"></div>
        <div class="col-xs-6">
          <uib-alert ng-repeat="alert in list.alerts.alertList"
              type="{{alert.type}}"
              close="alert.close()"
              dismiss-on-timeout="{{alert.dismiss}}">
            {{alert.msg}}
          </uib-alert>
        </div>
        <div class="col-xs-3"></div>
      </div>

      <h1>User anzeigen</h1>

      <form>
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon"><i class="fa fa-search"></i></div>
            <input type="text" class="form-control"
                placeholder="Suchen" ng-model="list.searchText">
          </div>
        </div>
      </form>

      <table class="table table-hover sortable">
        <!-- Titelzeile der Tabelle mit Sortiermöglichkeiten -->
        <tr>
          <th ng-click="list.sortClick('cn')">
            cn
            <span ng-show="list.sortField === 'cn'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}">
            </span>
          </th>
          <th ng-click="list.sortClick('displayName')">
            Name
            <span ng-show="list.sortField === 'displayName'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}">
            </span>
          </th>
          <th ng-click="list.sortClick('mail')">
            E-Mail
            <span ng-show="list.sortField === 'mail'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}">
            </span>
          </th>
        </tr>

        <!-- Tabelleneintrag für Benutzer -->
        <tr ng-repeat-start="user in list.userData
              | orderBy:list.sortType:list.sortReverse
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
          group-data="list.groupEditServ.groupData">
      </usradm-group-add-modal>
    </div>

    <!-- data for the user list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonUsers">
      <?php echo json_encode($users, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>
    <script type="application/json" json-data id="jsonGroups">
      <?php echo json_encode($groupOus, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>

<?php include('html_bottom.inc.php'); ?>
