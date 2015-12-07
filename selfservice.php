<?php
require_once('config.inc.php');
session_start();
define('USE_ANGULAR', true);
?>

<?php include('html_head.inc.php'); ?>

    <div class="container" ng-controller="SelfServiceController as serv">
      <h1><?php echo PAGETITLE . ' &mdash; Self Service'; ?></h1>

      <div id="step1" class="container" ng-if="!serv.loggedIn">
        <form class="form-signin">
          <h2 class="form-signin-heading">Bitte einloggen</h2>
          <label for="inputCn" class="sr-only">Benutzername</label>
          <input type="text" id="inputCn" name="inputCn"
              class="form-control" placeholder="Benutzername"
              required autofocus ng-model="serv.cn"></input>
          <label for="inputPassword" class="sr-only">Passwort</label>
          <input type="password" id="inputPassword" name="inputPassword"
              class="form-control" placeholder="Passwort"
              required ng-model="serv.pw"></input>
          <button type="submit" id="signIn" name="signIn"
              class="btn btn-lg btn-primary btn-block"
              ng-click="serv.login()">
            Login
          </button>
        </form>
        <div class="alert alert-danger" ng-if="serv.loginMessage">
          {{serv.loginMessage}}
        </div>
      </div>

      <div id="step2" class="container" ng-if="serv.loggedIn">
        <usradm-edit-user user="serv.user"
          closable="false" editable="false">
        </usradm-edit-user>

        <form class="form-horizontal">
          <div class="form-group has-feedback"
              ng-class="{'has-warning': !serv.pwd1}">
            <label class="control-label col-sm-4" for="pwd1">
              Passwort:
            </label>
            <div class="col-sm-8">
              <input type="password" class="form-control"
                  id="pwd1" ng-model="serv.pwd1"
                  ng-disabled="serv.formDisable()" />
              <span class="glyphicon form-control-feedback"
                  ng-show="!serv.pwd1"
                  ng-class="{'glyphicon-remove': !serv.pwd1}">
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
                  id="pwd2" ng-model="serv.pwd2"
                  ng-disabled="serv.formDisable()" />
              <span class="glyphicon form-control-feedback"
                  ng-show="serv.pwd1"
                  ng-class="{'glyphicon-ok': serv.isSamePw()
                      && !serv.formDisable(),
                    'glyphicon-remove': serv.pwd1 && !serv.isSamePw()}">
              </span>
            </div>
          </div>

          <div class="form-group">
            <button type="submit"
                class="btn btn-primary btn-sm rbtnMargin"
                ng-click="serv.changePassword()"
                ng-disabled="!serv.pwd1 || !serv.isSamePw() ||
                  serv.formDisable()">
              Passwort ändern
            </button>
            <span class="fa fa-refresh"
                ng-show="serv.pwChanging"
                ng-class="{'fa-spin' : serv.pwChanging}"></span>
            <span class="fa fa-check"
                ng-show="serv.pwChanged"></span>
            <span class="fa fa-times"
                ng-show="serv.pwChangeFailed"></span>
          </div>

          <div class="alert alert-danger" ng-if="serv.pwChangeMessage">
            {{serv.pwChangeMessage}}
          </div>
        </form>
      </div>

    </div>

<?php include('html_bottom.inc.php'); ?>
