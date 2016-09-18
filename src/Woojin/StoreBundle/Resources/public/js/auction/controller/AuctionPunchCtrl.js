'use strict';

/* Controllers */

auctionCtrl.controller('AuctionPunchCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) { 

    document.title = '競拍刷入頁面';

    $scope.broadcastEvent = function () {
      $scope.$broadcast('refreshProducts', {});
    };
}]);