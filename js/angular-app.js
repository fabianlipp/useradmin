(function(){
  var userlistApp = angular.module('userlistApp', ['ngAnimate']);

  userlistApp.controller('ListController', function($http) {
    this.sortField = 'cn';
    this.sortReverse = false;
    this.searchText = '';

    this.userData = JSON.parse(document.getElementById('jsonUsers').textContent);

    for (var i = 0; i < this.userData.length; i++) {
      this.userData[i].userId = i;
      this.userData[i].expanded = false;
      this.userData[i].details = null;
      this.userData[i].detailsLoaded = false;
    }

    this.sortClick = function(field) {
      if (this.sortField === field) {
        this.sortReverse = !this.sortReverse;
      }
      this.sortField = field;
    };

    this.expandClick = function(userId) {
      this.userData[userId].expanded = !this.userData[userId].expanded;
      if (!this.userData[userId].detailsLoaded) {
        this.loadDetail(userId);
      }
    }

    this.loadDetail = function(userId) {
      var that = this;
      $http.get('getUserDetails.json.php',
          {params: {dn: this.userData[userId].dn}})
          .success(function(data) {
        that.userData[userId].details = data;
        that.userData[userId].detailsLoaded = true;
      })
    }

    this.formatJson = function(json_str) {
      return JSON.stringify(json_str, undefined, 2);
    }
  });

})();
