<?php
require_once('config.inc.php');

require_once('ldap.inc.php');
require_once('user.inc.php');
require_once('groupOu.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);
$groupOus = GroupOu::readGroupOus($ldapconn);

ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>

  <body>

<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="ListController as list">
      <!-- show alerts -->
      <div id="alert-container" class="container">
        <div class="col-xs-3"></div>
        <div class="col-xs-6">
          <uib-alert ng-repeat="alert in list.alerts"
              type="{{alert.type}}"
              close="list.closeAlert($index)"
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
            <div class="well">
              <a href="#" class="close" aria-label="close"
                  ng-click="list.expandClick(user)">
                &times;
              </a>
              <div style="text-align: center" ng-if="user.loading">
                <span class="fa fa-refresh"
                    ng-class="{'fa-spin' : user.loading}"></span>
              </div>
              <table class="userdetails" ng-if="!user.loading">
                <tr>
                  <th>cn:</th>
                  <td>{{user.cn}}</td>
                </tr>
                <tr>
                  <th>Name:</th>
                  <td>{{user.displayName}}</td>
                </tr>
                <tr>
                  <th>E-Mail:</th>
                  <td>
                    <span editable-text="user.mail"
                        e-form="mailBtnForm"
                        onbeforesave="list.updateMail($data, $form, user)"
                        onshow="list.resetEditableForm($form)">
                      {{user.mail}}
                    </span>
                    <span class="fa fa-refresh"
                        ng-show="mailBtnForm.loading"
                        ng-class="{'fa-spin' : mailBtnForm.loading}"></span>
                    <span class="fa fa-check"
                        ng-show="mailBtnForm.success"></span>
                    <span class="fa fa-times"
                        ng-show="mailBtnForm.fail"></span>
                    <span class="glyphicon glyphicon-pencil clickable"
                        ng-click="mailBtnForm.$show()"
                        ng-hide="mailBtnForm.$visible || mailBtnForm.loading">
                    </span>
                  </td>
                </tr>
                <tr>
                  <th class="lblGruppen">Gruppen:</th>
                  <td>
                    <ul ng-if="user.details.groups.length">
                      <li ng-repeat="group in user.details.groups">
                        {{group.cn}}
                        <span class="small">({{group.description}})</span>
                        <span class="fa fa-refresh"
                            ng-show="list.groupIsRemoving(user, group)"
                            ng-class="{'fa-spin' :
                                list.groupIsRemoving(user, group)}"></span>
                        <span class="glyphicon glyphicon-minus clickable"
                            ng-click="list.removeGroupFromUser(user, group)">
                        </span>
                      </li>
                    </ul>
                        <span class="fa fa-refresh"
                            ng-show="list.groupIsAdding(user)"
                            ng-class="{'fa-spin' :
                                list.groupIsAdding(user)}"></span>
                    <span class="glyphicon glyphicon-plus clickable"
                        ng-click="list.addGroup(user)">
                    </span>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>

      <!-- Modal-Dialog zur Gruppenauswahl zum Hinzufügen -->
      <div id="groupAddModal" class="modal fade" role="dialog">
          <div class="modal-dialog">

          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close"
                  data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Gruppe hinzufügen</h4>
            </div>
            <div class="modal-body">
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
                          ng-repeat="group in ou.groups"
                          ng-show="!list.addGroupUserHasGroup(group)"
                          ng-click="list.addGroupToUser(group)">
                        <h5 class="list-group-item-heading">
                          {{group.cn}}
                          <span class="small">
                            ({{group.dn}})
                          </span>
                        </h5>
                        <p class="list-group-item-text">
                          {{group.description}}
                        </p>
                      </li>
                    </ul>
                    <div class="panel-body" ng-if="!ou.groups.length">
                      Keine Gruppen in dieser Kategorie.
                    </div>
                  </div> <!-- panel-collapse -->
                </div> <!-- panel -->
              </div> <!-- panel-group -->
            </div> <!-- modal-body -->
            <div class="modal-footer">
            </div>
          </div>

        </div>
      </div>

      <!-- DEBUG -->
      <!--<p><pre><?php print_r($users); ?></pre></p> -->
    </div>

    <!-- data for the user list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonUsers">
      <?php echo json_encode($users, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>
    <script type="application/json" json-data id="jsonGroups">
      <?php echo json_encode($groupOus, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>

<?php include('html_bottom.inc.php'); ?>
