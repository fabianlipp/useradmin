(function(){
  var useradminApp = angular.module('useradminApp',
      ['ngAnimate', 'xeditable', 'ui.bootstrap']);

  useradminApp.run(function(editableOptions) {
    editableOptions.theme = 'bs3';
  });

  useradminApp.factory('alertsService', function() {
    var alertsService = {};

    alertsService.alertList = [];
    alertsService.closeAlert = function(index) {
      alertsService.alertList.splice(index, 1);
    };
    alertsService.push = function(msg) {
      msg.close = function() {
        var list = alertsService.alertList;
        list.splice(list.indexOf(msg), 1);
      };
      alertsService.alertList.push(msg);
    }

    return alertsService;
  });

  useradminApp.controller('UserlistController',
      function($http, alertsService, groupEditService, editUserService) {
    this.alerts = alertsService;
    this.groupEditServ = groupEditService;
    this.editUserServ = editUserService;

    this.sortField = 'cn';
    this.sortReverse = false;
    this.searchText = '';

    this.pwChangeUser = false;
    this.deleteSelectedUser = false;

    this.userData = JSON.parse(document.getElementById('jsonUsers').textContent);
    this.pwd1 = '';
    this.pwd2 = '';

    for (var i = 0; i < this.userData.length; i++) {
      var user = this.userData[i];
      user.expanded = false;
      user.detailsLoaded = false;
      user.loading = false;
      user.pwChanged = false;
      user.pwChanging = false;
      user.userDeleting = false;
      user.index = i;
    }

    this.sortClick = function(field) {
      if (this.sortField === field) {
        this.sortReverse = !this.sortReverse;
      }
      this.sortField = field;
    };

    this.expandClick = function(user) {
      user.expanded = !user.expanded;
      if (!user.detailsLoaded) {
        this.loadDetail(user);
      }
    };

    this.loadDetail = function(user) {
      var that = this;
      user.loading = true;
      $http.get('ajax/getUserDetails.json.php',
          {params: {dn: user.dn}})
          .success(function(data) {
        user.groups = data.groups;
        user.sn = data.sn;
        user.givenName = data.givenName;
        user.detailsLoaded = true;
        user.loading = false;
        user.groupDns = {};
        user.groups.map(function(item) {
          user.groupDns[item.dn] = item;
        });
      });
    };

    this.formatJson = function(json_str) {
      return JSON.stringify(json_str, undefined, 2);
    };

    this.showChangePw = function(user) {
      this.pwChangeUser = user;
      var el = angular.element('#pwd1');
      this.pwd1 = '';
      this.pwd2 = '';
      user.pwChanged = false;
      angular.element('#pwChangeModal').modal('show');
    };

    this.isSamePw = function() {
      return (this.pwd1 == this.pwd2);
    };

    this.changePassword = function() {
      if (!this.pwd1 || this.pwd1 != this.pwd2) {
        return;
      }
      var that = this;
      var user = this.pwChangeUser;
      user.pwChanging = true;
      angular.element('#pwChangeModal').modal('hide');
      $http.post('ajax/changePassword.json.php',
          {'dn': user.dn,
            'newPassword': this.pwd1})
          .then(function(response) {
            // success
            user.pwChanging = false;
            user.pwChanged = true;
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzerpasswort für ' + user.cn
                    + ' geändert.',
              dismiss: 5000});
          }, function(response) {
            // error
            user.pwChanging = false;
            user.pwChanged = false;
            that.alerts.push(
              {type: 'danger',
                msg: 'Benutzerpasswort für ' + user.cn
                    + ' konnte nicht geändert werden:'
                    + response.data.detail});
          });
    };

    this.showDeleteUser = function(user) {
      this.deleteSelectedUser = user;
      angular.element('#userDeleteModal').modal('show');
    };

    this.deleteUser = function() {
      var that = this;
      var user = this.deleteSelectedUser;
      user.userDeleting = true;
      angular.element('#userDeleteModal').modal('hide');
      $http.post('ajax/deleteUser.json.php',
          {'dn': user.dn})
          .then(function(response) {
            // success
            user.userDeleting = false;
            delete that.userData[user.index];
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn
                    + ' gelöscht.',
              dismiss: 5000});
          }, function(response) {
            // error
            user.userDeleting = false;
            that.alerts.push(
              {type: 'danger',
                msg: 'Benutzer ' + user.cn
                    + ' konnte nicht gelöscht werden:'
                    + response.data.detail});
          });
    };

  });



  useradminApp.directive('usradmEditText', function() {
    return {
      restrict: 'E',
      templateUrl: 'templates/editText.html',
      scope: {
        usradmField: '=usradmField',
        onBeforesaveFn: '&onbeforesave'
      },
      link: function(scope, element, attrs) {
        scope.resetEditableForm = function (form) {
          form.loading = false;
          form.success = false;
          form.fail = false;
        };
      }
    };
  });



  useradminApp.directive('usradmGroupAddListAccordion', function(editUserService) {
    return {
      restrict: 'E',
      templateUrl: 'templates/groupAddList.html',
      scope: {
        groupData: '=groupData',
      },
      link: function(scope, elemet, attrs) {
        scope.editUserService = editUserService;
      }
    };
  });



  useradminApp.factory('groupEditService',
      function($http, alertsService) {
    alerts = alertsService;
    var serv = {};

    var jsonGroupEl = document.getElementById('jsonGroups');
    if (jsonGroupEl) {
      serv.groupData = JSON.parse(jsonGroupEl.textContent);
    }

    serv.addGroupToUser = function(user, group, groupAdding) {
      groupAdding[user.dn] = true;
      angular.element('#groupAddModal').modal('hide');
      $http.post('ajax/addUserGroup.json.php',
          {'userdn': user.dn,
            'groupdn': group.dn})
          .then(function(response) {
            // success
            user.groups.push(group);
            user.groupDns[group.dn] = group;
            groupAdding[user.dn] = false;
            alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn + ' zu Gruppe '
                    + group.cn + ' hinzugefügt',
              dismiss: 5000});
          }, function(response) {
            // error
            groupAdding[user.dn] = false;
            alerts.push(
              {type: 'danger',
                msg: 'Konnte Benutzer ' + user.cn + ' nicht zu Gruppe '
                    + group.cn + ' hinzufügen: ' + response.data.detail});
          });
    };

    serv.removeGroupFromUser = function(user, group, groupRemoving) {
      groupRemoving[user.dn][group.dn] = true;
      $http.post('ajax/removeUserGroup.json.php',
          {'userdn': user.dn,
            'groupdn': group.dn})
          .then(function(response) {
            // success
            user.groups.splice(user.groups.indexOf(group), 1)
            delete user.groupDns[group.dn];
            groupRemoving[user.dn][group.dn] = false;
            alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn + ' aus Gruppe '
                    + group.cn + ' entfernt',
              dismiss: 5000});
          }, function(response) {
            // error
            groupRemoving[user.dn][group.dn] = false;
            alerts.push(
              {type: 'danger',
                msg: 'Konnte Benutzer ' + user.cn + ' nicht aus Gruppe '
                    + group.cn + ' entfernen: ' + response.data.detail});
          });
    };

    return serv;
  });



  useradminApp.directive('usradmEditUser', function(editUserService) {
    return {
      restrict: 'E',
      templateUrl: 'templates/editUser.html',
      scope: {
        user: '=user',
        expandClickFn: '&expandClick'
      },
      link: function(scope, elemet, attrs) {
        scope.editUserService = editUserService;
        scope.closable = (attrs.closable === 'true');
        scope.editable = (attrs.editable === 'true');
      }
    };
  });



  useradminApp.factory('editUserService',
      function($http, alertsService, groupEditService) {
    var alerts = alertsService;
    var groupEditServ = groupEditService;
    var userAddGroup = false;
    var groupAdding = {};
    var groupRemoving = {};

    var serv = {};

    serv.updateDetail = function(field, data, form, user) {
      form.loading = true;
      form.success = false;
      form.fail = false;
      $http.post('ajax/changeUserDetail.json.php',
          {'dn': user.dn,
            'field': field,
            'newValue': data})
          .then(function(response) {
            // success
            form.loading = false;
            form.success = true;
            if (typeof response.data.val != 'undefined') {
              console.log(user);
              console.log(field);
              console.log(user[field]);
              user[field] = response.data.val;
            }
          }, function(response) {
            // error
            form.loading = false;
            form.fail = true;
            if (typeof response.data.val != 'undefined') {
              user[field] = response.data.val;
            }
          });
      return false;
    };

    serv.addGroup = function(user) {
      userAddGroup = user;
      angular.element('#groupAddModal').modal('show');
    };

    serv.userHasGroup = function(group) {
      if (!userAddGroup) {
        return false;
      }
      return userAddGroup.groupDns.hasOwnProperty(group.dn);
    };

    serv.addGroupToUser = function(group) {
      groupEditService.addGroupToUser(userAddGroup, group, groupAdding);
      angular.element('#groupAddModal').modal('hide');
    };

    serv.removeGroupFromUser = function(user, group) {
      if (!(user.dn in groupRemoving)) {
        groupRemoving[user.dn] = {};
      }
      groupRemoving[user.dn][group.dn] = true;
      groupEditService.removeGroupFromUser(user, group, groupRemoving);
    };

    serv.groupIsRemoving = function(user, group) {
      return (user.dn in groupRemoving
          && group.dn in groupRemoving[user.dn]
          && groupRemoving[user.dn][group.dn]);
    };

    serv.groupIsAdding = function(user) {
      return (user.dn in groupAdding && groupAdding[user.dn]);
    };

    return serv;
  });



  useradminApp.directive('usradmGroupAddModal', function() {
    return {
      restrict: 'E',
      templateUrl: 'templates/groupAddModal.html',
      scope: {
        groupData: '=groupData'
      }
    };
  });



  useradminApp.controller('GrouplistController', function() {
    this.groupData = JSON.parse(
        document.getElementById('jsonGroupOus').textContent);
  });



  useradminApp.controller('AddUserController',
      function($http, alertsService, groupEditService) {
    this.alerts = alertsService;
    this.groupEditServ = groupEditService;

    var mailSettingsEl = document.getElementById('mailSettings');
    if (mailSettingsEl) {
      this.mailSettings = JSON.parse(mailSettingsEl.textContent);
    }

    this.step = 1;
    this.userform = {
      "cn": "",
      "mail": "",
      "sn": "",
      "givenName": ""
    };

    this.user = null;
    this.userpassword = null;

    this.mailform = {
      "sender": this.mailSettings.sender,
      "recipient": "",
      "subject": this.mailSettings.subject,
      "mailbody": ""
    };

    this.mailsending = false;
    this.mailsuccess = false;
    this.mailfailure = false;


    this.suggestUsername = function() {
      var cnMixedCase = this.userform.givenName + this.userform.sn;
      this.userform.cn = cnMixedCase.toLowerCase();
    };

    this.completeStep1 = function() {
      var that = this;
      $http.post('ajax/addUser.json.php',
          {'cn': this.userform.cn,
            'mail':  this.userform.mail,
            'sn':  this.userform.sn,
            'givenName':  this.userform.givenName})
        .then(function(response) {
            // success
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer angelegt',
              dismiss: 5000});
            that.userpassword = response.data.password;
            that.user = response.data.user;
            that.user.groupDns = {};
            that.user.groups.map(function(item) {
              that.user.groupDns[item.dn] = item;
            });
            that.step = 2;
          }, function(response) {
            // error
            var responsemsg;
            if (typeof response.data == 'object') {
              responsemsg = response.data.detail;
            } else {
              responsemsg = response.data;
            }
            that.alerts.push(
              {type: 'danger',
                msg: 'Benutzer konnte nicht angelegt werden: '
                    + responsemsg});
          });

    };

    this.completeStep2 = function() {
      var f = this.mailform;
      f.recipient = this.user.mail;
      var context = {
        user: this.user,
        sendername: this.mailSettings.sendername,
        userpassword: this.userpassword
      };
      f.mailbody = Mark.up(this.mailSettings.template, context);
      console.log(f.mailbody);
      this.step = 3;
    };

    this.sendMail = function() {
      this.mailsending = true;
      this.mailsuccess = false;
      this.mailfailure = false;
      var that = this;
      $http.post('ajax/sendMail.json.php',
          {'mailform': this.mailform
            })
        .then(function(response) {
          that.mailsuccess = true;
          that.mailsending = false;
        }, function(response) {
          that.mailfailure = true;
          that.mailsending = false;
        });

    };
  });



  useradminApp.controller('SelfServiceController',
      function($http) {

    this.cn = "";
    this.pw = "";
    this.loggedIn = false;
    this.loginMessage = "";

    this.user = null;

    this.pwd1 = "";
    this.pwd2 = "";
    this.pwChanging = false;
    this.pwChanged = false;
    this.pwChangeFailed = false;
    this.pwChangeMessage = "";

    this.login = function() {
      var that = this;
      $http.post('ajax/selfserviceLogin.json.php',
          {'cn': this.cn,
            'pw': this.pw})
        .then(function(response) {
          that.loggedIn = true;
          that.user = response.data;
          that.loginMessage = "";
        }, function(response) {
          if (response.status == 403) {
            that.loginMessage = "Ungültiger Benutzername oder Passwort";
          } else {
            that.loginMessage = "Login nicht erfolgreich.";
          }
        });
    };

    this.isSamePw = function() {
      return (this.pwd1 == this.pwd2);
    };

    this.formDisable = function() {
      return this.pwChanging || this.pwChanged || this.pwChangeFailed;
    };

    this.changePassword = function() {
      if (!this.pwd1 || this.pwd1 != this.pwd2 || this.pwChanging) {
        return;
      }
      var that = this;
      this.pwChanging = true;
      this.pwChanged = false;
      $http.post('ajax/changePassword.json.php',
          {'dn': this.user.dn,
            'newPassword': this.pwd1},
          {params: {'destroySession': true}})
        .then(function(response) {
          that.pwChanging = false;
          that.pwChanged = true;
        }, function(response) {
          that.pwChangeMessage = response.data.message;
          that.pwChanging = false;
          that.pwChangeFailed = true;
        });
    };
  });



  useradminApp.directive('usradmAlertContainer', function() {
    return {
      restrict: 'E',
      templateUrl: 'templates/alertContainer.html',
      scope: {
        alerts: '=alerts'
      }
    };
  });



  useradminApp.directive('usradmUserlistSearch', function() {
    return {
      restrict: 'E',
      templateUrl: 'templates/userlistSearch.html',
      scope: {
        list: '=list'
      }
    };
  });



  useradminApp.directive('usradmUserlistHeader', function() {
    return {
      restrict: 'A',
      templateUrl: 'templates/userlistHeader.html',
      scope: {
        list: '=list'
      }
    };
  });
})();
