'use strict';

/* Controllers */

auctionCtrl.controller('ProductsCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) { 
    $scope.products = [];

    $scope.$on('refreshProducts', function (event, data) {
      $scope.refresh();
    });

    $scope.refresh = function () {
      $http.get(Routing.generate('api_list_auction'))
      .success(function (res) {
        $scope.products = res;
      });
    };

    $scope.genProductUrl = function (sn) {
      return Routing.generate('goods_edit_v2_from_sn', {sn: sn});
    };
}]);