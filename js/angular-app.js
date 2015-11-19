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
      function($http, alertsService, groupEditService) {
    this.alerts = alertsService;
    this.groupEditServ = groupEditService;

    this.sortField = 'cn';
    this.sortReverse = false;
    this.searchText = '';

    this.userAddGroup = false;
    this.pwChangeUser = false;

    this.groupRemoving = {};
    this.groupAdding = {};

    this.userData = JSON.parse(document.getElementById('jsonUsers').textContent);
    this.pwd1 = '';
    this.pwd2 = '';

    for (var i = 0; i < this.userData.length; i++) {
      var user = this.userData[i];
      user.expanded = false;
      user.details = null;
      user.detailsLoaded = false;
      user.loading = false;
      user.pwChanged = false;
      user.pwChanging = false;
      this.groupRemoving[user.dn] = {};
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
        user.details = data;
        user.detailsLoaded = true;
        user.loading = false;
        user.groupDns = {};
        user.details.groups.map(function(item) {
          user.groupDns[item.dn] = item;
        });
      });
    };

    this.formatJson = function(json_str) {
      return JSON.stringify(json_str, undefined, 2);
    };

    this.updateMail = function(data, form, user) {
      form.loading = true;
      form.success = false;
      form.fail = false;
      $http.post('ajax/changeUserDetail.json.php',
          {'dn': user.dn,
            'newMail': data})
          .then(function(response) {
            // success
            form.loading = false;
            form.success = true;
            if (typeof response.data.mail != 'undefined') {
              user.mail = response.data.mail;
            }
          }, function(response) {
            // error
            form.loading = false;
            form.fail = true;
            if (typeof response.data.mail != 'undefined') {
              user.mail = response.data.mail;
            }
          });
      return false;
    };

    this.updateDisplayName = function(data, form, user) {
      form.loading = true;
      form.success = false;
      form.fail = false;
      $http.post('ajax/changeUserDetail.json.php',
          {'dn': user.dn,
            'newDisplayName': data})
          .then(function(response) {
            // success
            form.loading = false;
            form.success = true;
            if (typeof response.data.displayName != 'undefined') {
              user.displayName = response.data.displayName;
            }
          }, function(response) {
            // error
            form.loading = false;
            form.fail = true;
            if (typeof response.data.displayName != 'undefined') {
              user.displayName = response.data.displayName;
            }
          });
      return false;
    };

    this.addGroup = function(user) {
      this.userAddGroup = user;
      angular.element('#groupAddModal').modal('show');
    };

    this.addGroupUserHasGroup = function(group) {
      if (!this.userAddGroup) {
        return false;
      }
      return this.userAddGroup.groupDns.hasOwnProperty(group.dn);
    };

    this.addGroupToUser = function(group) {
      var user = this.userAddGroup;
      groupEditService.addGroupToUser(user, group, this.groupAdding);
      angular.element('#groupAddModal').modal('hide');
    };

    this.removeGroupFromUser = function(user, group) {
      groupEditService.removeGroupFromUser(user, group, this.groupRemoving);
      this.groupRemoving[user.dn][group.dn] = true;
    };

    this.groupIsRemoving = function(user, group) {
      return this.groupRemoving[user.dn][group.dn];
    };

    this.groupIsAdding = function(user) {
      return this.groupAdding[user.dn];
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
    }

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



  useradminApp.directive('groupAddListAccordion', function() {
    return {
      restrict: 'E',
      templateUrl: 'templates/groupAddList.html',
      scope: {
        groupData: '=groupData',
        userHasGroupFn: '&userHasGroupFn',
        userAddToGroupFn: '&userAddToGroupFn'
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
            user.details.groups.push(group);
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
            user.details.groups.splice(user.details.groups.indexOf(group), 1)
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



  useradminApp.controller('GrouplistController', function() {
    this.groupData = JSON.parse(
        document.getElementById('jsonGroupOus').textContent);
  });



  useradminApp.controller('AddUserController', function($http, alertsService) {
    this.alerts = alertsService;

    this.step = 1;
    this.user = {
      "cn": "",
      "mail": "",
      "sn": "",
      "givenName": ""
    };

    this.groupData = JSON.parse(
        document.getElementById('jsonGroups').textContent);

    this.completeStep1 = function() {
      var that = this;
      $http.post('ajax/addUser.json.php',
          {'cn': this.user.cn,
            'mail':  this.user.mail,
            'sn':  this.user.sn,
            'givenName':  this.user.givenName})
        .then(function(response) {
            // success
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer angelegt',
              dismiss: 5000});
            that.step = 2;
          }, function(response) {
            // error
            that.alerts.push(
              {type: 'danger',
                msg: 'Benutzer konnte nicht angelegt werden: '
                    + response.data.detail});
          });

    }
  });
})();
