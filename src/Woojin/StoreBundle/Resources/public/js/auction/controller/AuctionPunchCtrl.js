'use strict';

/* Controllers */

auctionCtrl.controller('AuctionPunchCtrl', ['AuctionHelper', '$scope', '$routeParams', '$http',
  function (AuctionHelper, $scope, $routeParams, $http) {

    document.title = '競拍刷入頁面';

    $scope.broadcastEvent = function () {
      $scope.$broadcast('refreshProducts', {});
    };

    $scope.getSn = function (product) {
        return AuctionHelper.getExtensibleSn(product);
    };
}]);
