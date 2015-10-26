(function(){
  var userlistApp = angular.module('userlistApp',
      ['ngAnimate', 'xeditable', 'ui.bootstrap']);

  userlistApp.run(function(editableOptions) {
    editableOptions.theme = 'bs3';
  });

  userlistApp.controller('ListController', function($http) {
    this.sortField = 'cn';
    this.sortReverse = false;
    this.searchText = '';

    this.userAddGroup = false;

    this.groupRemoving = {};
    this.groupAdding = {};

    this.userData = JSON.parse(document.getElementById('jsonUsers').textContent);
    this.groupData = JSON.parse(document.getElementById('jsonGroups').textContent);

    this.alerts = [];
    this.closeAlert = function(index) {
      this.alerts.splice(index, 1);
    };

    for (var i = 0; i < this.userData.length; i++) {
      var user = this.userData[i];
      user.expanded = false;
      user.details = null;
      user.detailsLoaded = false;
      user.loading = false;
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
      $http.get('getUserDetails.json.php',
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
      $http.post('changeUserDetail.php',
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
      var that = this;
      this.groupAdding[user.dn] = true;
      angular.element('#groupAddModal').modal('hide');
      $http.post('addUserGroup.json.php',
          {'userdn': this.userAddGroup.dn,
            'groupdn': group.dn})
          .then(function(response) {
            // success
            console.log("success");
            console.log(response);
            user.details.groups.push(group);
            user.groupDns[group.dn] = group;
            that.groupAdding[user.dn] = false;
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn + ' zu Gruppe '
                    + group.cn + ' hinzugefügt',
              dismiss: 5000});
          }, function(response) {
            // error
            console.log("error");
            console.log(response);
            that.groupAdding[user.dn] = false;
            that.alerts.push(
              {type: 'danger',
                msg: 'Konnte Benutzer ' + user.cn + ' nicht zu Gruppe '
                    + group.cn + ' hinzufügen'});
          });
    };

    this.removeGroupFromUser = function(user, group) {
      var that = this;
      this.groupRemoving[user.dn][group.dn] = true;
      $http.post('removeUserGroup.json.php',
          {'userdn': user.dn,
            'groupdn': group.dn})
          .then(function(response) {
            // success
            console.log("success");
            console.log(response);
            user.details.groups.splice(user.details.groups.indexOf(group), 1)
            delete user.groupDns[group.dn];
            that.groupRemoving[user.dn][group.dn] = false;
            that.alerts.push(
              {type: 'success',
                msg: 'Benutzer ' + user.cn + ' aus Gruppe '
                    + group.cn + ' entfernt',
              dismiss: 5000});
          }, function(response) {
            // error
            console.log("error");
            console.log(response);
            that.groupRemoving[user.dn][group.dn] = false;
            that.alerts.push(
              {type: 'danger',
                msg: 'Konnte Benutzer ' + user.cn + ' nicht aus Gruppe '
                    + group.cn + ' entfernen'});
          });
    };

    this.groupIsRemoving = function(user, group) {
      return this.groupRemoving[user.dn][group.dn];
    };

    this.groupIsAdding = function(user) {
      return this.groupAdding[user.dn];
    };
  });



  userlistApp.directive('usradmEditText', function() {

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

})();
