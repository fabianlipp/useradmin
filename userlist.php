<?php
require_once('config.inc.php');

require_once('ldap.inc.php');
require_once('user.inc.php');
session_start();

$ldapconn = ldap_bind_session();
$users = User::readUsers($ldapconn);

ldap_close($ldapconn);

define('USE_ANGULAR', true);

?>
<?php include('html_head.inc.php'); ?>

  <body>

<?php include('navigation.inc.php'); ?>

    <div class="container" ng-controller="ListController as list">
      <h1>User anzeigen</h1>

      <!--<div class="alert alert-info">
        <p>Sort Field: {{list.sortField}}</p>
        <p>Sort Reverse: {{list.sortReverse}}</p>
        <p>Search Query: {{list.searchText}}</p>
      </div>-->

      <form>
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon"><i class="fa fa-search"></i></div>
            <input type="text" class="form-control" placeholder="Suchen" ng-model="list.searchText">
          </div>
        </div>
      </form>

      <table class="table table-hover sortable">
        <tr>
          <th ng-click="list.sortClick('cn')">
            cn
            <span ng-show="list.sortField === 'cn'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}"
            />
          </th>
          <th ng-click="list.sortClick('displayName')">
            Name
            <span ng-show="list.sortField === 'displayName'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}"
            />
          </th>
          <th ng-click="list.sortClick('mail')">
            E-Mail
            <span ng-show="list.sortField === 'mail'"
                class="fa fa-caret-down"
                ng-class="{'fa-caret-down': !list.sortReverse,
                  'fa-caret-up': list.sortReverse}"
            />
          </th>
        </tr>

        <tr ng-repeat-start="user in list.userData
              | orderBy:list.sortType:list.sortReverse
              | filter:list.searchText"
            ng-if="!user.expanded"
            ng-click="list.expandClick(user.userId)">
          <td>{{user.cn}}</td>
          <td>{{user.displayName}}</td>
          <td>{{user.mail}}</td>
          <!--<td>{{user.expanded}}</td>
          <td>{{user.detailLoaded}}</td>-->
        </tr>

        <tr ng-repeat-end="" ng-if="user.expanded">
          <td colspan="3">
            <div class="well">
              <a href="#" class="close" aria-label="close"
                  ng-click="list.expandClick(user.userId)">
                &times;
              </a>
              <table>
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
                    <span class="glyphicon glyphicon-pencil fieldEditBtn"
                        ng-click="mailBtnForm.$show()"
                        ng-hide="mailBtnForm.$visible || mailBtnForm.loading">
                    </span>
                  </td>
                </tr>
                <tr>
                  <th class="lblGruppen">Gruppen:</th>
                  <td>
                    <ul>
                      <li ng-repeat="group in user.details.groups">
                        {{group.cn}}
                        <span class="small">({{group.description}})</span>
                      </li>
                    </ul>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>

<?php /*      <ul class="list-group">
<?php foreach ($users as $user) { ?>
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
</ul> */ ?>

      <!-- DEBUG -->
      <p><pre><?php print_r($users); ?></pre></p>
    </div>

    <!-- data for the user list (is then parsed by AngularJS) -->
    <script type="application/json" json-data id="jsonUsers">
      <?php echo json_encode($users, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT); ?>
    </script>

<?php include('html_bottom.inc.php'); ?>
