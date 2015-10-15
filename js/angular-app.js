(function(){
  var userlistApp = angular.module('userlistApp', ['ngAnimate']);

  userlistApp.controller('ListController', function() {
    this.sortField = 'cn';
    this.sortReverse = false;
    this.searchText = '';

    this.userData = JSON.parse(document.getElementById('jsonUsers').textContent);

    for (var i = 0; i < this.userData.length; i++) {
      this.userData[i].userId = i;
      this.userData[i].expanded = false;
      this.userData[i].detailLoaded = false;
    }

    this.sortClick = function(field) {
      if (this.sortField === field) {
        this.sortReverse = !this.sortReverse;
      }
      this.sortField = field;
    };

    this.expandClick = function(userId) {
      this.userData[userId].expanded = !this.userData[userId].expanded;
    }
  });

})();
