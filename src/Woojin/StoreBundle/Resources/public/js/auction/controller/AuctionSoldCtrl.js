'use strict';

/* Controllers */

auctionCtrl.controller('AuctionSoldCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) {
    document.title = '競拍銷售頁面';

    $scope.showResponse = {};
    $scope.soldResponse = {};
    $scope.cancelResponse = {};
    $scope.productSn = '';
    $scope.price = '';
    $scope.mobil = '';
    $scope.isAu = true;

    $scope.$watch('showResponse', function(newValue, oldValue) {
        if (1 === newValue.status) {
          setTimeout(function () {$('input[name="price"]').focus()}, 300);
        }
    });

    $scope.config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    };

    $scope.find = function (sn) {
      $http.get(Routing.generate('api_auction_show_bysn', {sn: sn})).success(function (auction) {
          $scope.showResponse = auction;
          $scope.cancelResponse = {};
      });
    };

    $scope.sold = function (sn, price, mobil) {
      var data = $.param({
        sn: sn,
        mobil: mobil,
        price: price,
        _method: 'PUT'
      });

      $http.post(Routing.generate('api_sold_auction'), data, $scope.config).success(function (res) {
        $scope.soldResponse = res;

        if ($scope.isSuccess(res)) {
          $scope.showResponse = {};
          $scope.cancelResponse = {};
          $scope.price = '';
          $scope.mobil = '';
          $scope.productSn = '';
        }
      });
    };

    $scope.getProfit = function (perc, price) {
      return perc * price;
    };

    $scope.drop = function () {
      $scope.soldResponse = {};
      $scope.showResponse = {};
      $scope.cancelResponse = {};
      $scope.productSn = '';

      setTimeout(function () {$('input[name="productSn"]').focus()}, 300);
    };

    $scope.cancelSold = function (sn) {
      var data = $.param({
        sn: sn,
        _method: 'PUT'
      });

      $http.post(Routing.generate('api_cancel_auction'), data, $scope.config).success(function (res) {
        if ($scope.isSuccess(res)) {
          $scope.soldResponse = {};
          $scope.cancelResponse = res;
          $scope.showResponse = res;
          $scope.productSn = res.auction.product.sn;
        }
      });
    };

    $scope.bindCustom = function () {

    };

    $scope.fetchCustom = function () {

    };

    $scope.fetchAuction = function () {

    };

    $scope.validNumber = function () {

    };

    $scope.clean = function (response) {
      response = {};
    };

    $scope.isSuccess = function (response) {
      return 1 === parseInt(response.status);
    };

    $scope.isFail = function (response) {
      return 0 === parseInt(response.status);
    };

    $scope.isEmpty = function (response) {
      return 'undefined' === typeof response;
    };

    $scope.genProductUrl = function (sn) {
      return Routing.generate('goods_edit_v2_from_sn', {sn: sn});
    };
}]);
