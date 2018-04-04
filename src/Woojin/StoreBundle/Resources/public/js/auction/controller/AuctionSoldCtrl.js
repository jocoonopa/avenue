'use strict';

/* Controllers */

auctionCtrl.controller('AuctionSoldCtrl', ['AuctionHelper', '$scope', '$routeParams', '$http',
  function (AuctionHelper, $scope, $routeParams, $http) {
    document.title = '競拍銷售頁面';

    $scope.defaultDate = new Date(1980, 5, 1);
    $scope.showResponse = {};
    $scope.soldResponse = {};
    $scope.cancelResponse = {};
    $scope.productSn = '';
    $scope.price = '';
    $scope.customMobil = '';
    $scope.note = '';
    $scope.tmpMobil = '';
    $scope.isAu = true;
    $scope.custom = {
      "name": '',
      "mobil": '',
      "email": '',
      "address": '',
      "sex": '',
      "birthday": '',
      "line_account": '',
      "facebook_account": '',
    };
    $scope.isGhostMobil = false;
    $scope.customHandleResponse = {};

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

    // WTF!??? success and then would make different??????
    // So suck and stupid....
    // http://blog.jyootai.com/blog/2015/10/13/angular-success-and-then-method.html
    $scope.loadCustomsMobils = function (mobil) {
        // if (5 > mobil.length || true === window.noblockUI) {
        //     return;
        // }
        //
        // window.noblockUI = true;
        //
        // return $http.get(Routing.generate('api_auction_custom_typeahead'), {params: { mobil: mobil}}).then(function (response) {
        //     window.noblockUI = false;
        //     var customs = response.data;
        //     return customs.map(function (custom) {
        //         return custom.mobil;
        //     });
        // });
    };

    var _sold = function (data) {
      $http.post(Routing.generate('api_sold_auction'), data, $scope.config).success(function (res) {
        $scope.soldResponse = res;

        if ($scope.isSuccess(res)) {
          $scope.showResponse = {};
          $scope.cancelResponse = {};
          $scope.price = '';
          $scope.productSn = '';
          $scope.customMobil = '';
        }
      });
    };

    $scope.sold = function (sn, price, mobil, note) {
      var data = $.param({
        sn: sn,
        mobil: mobil,
        price: price,
        note: note,
        _method: 'PUT'
      });

      $http.get(Routing.generate('api_auction_custom_typeahead'), {params: { mobil: mobil}})
      .success(function (customs) {
        $scope.isGhostMobil = 0 === customs.length;

        if (false === $scope.isGhostMobil) {
          return _sold(data);
        }
      });
    };

    $scope.createCustom = function () {
      var data = $.param({
        "name": $scope.custom.name,
        "mobil": $scope.custom.mobil,
        "email": $scope.custom.email,
        "address": $scope.custom.address,
        "sex": $scope.custom.sex,
        "birthday": $scope.custom.birthday,
        "line": $scope.custom.line_account,
        "facebook": $scope.custom.facebook_account,
      });

      $http.post(Routing.generate('api_auction_customer_create'), data, $scope.config).success(function (res) {
        $scope.custom = {};
        $scope.customHandleResponse = res;
        $scope.customHandleResponse.optype = 0;
        $scope.isGhostMobil = false;
      });
    };

    $scope.updateCustom = function () {
      var data = $.param({
        "name": $scope.custom.name,
        "mobil": $scope.custom.mobil,
        "email": $scope.custom.email,
        "address": $scope.custom.address,
        "sex": $scope.custom.sex,
        "birthday": $scope.custom.birthday,
        "line": $scope.custom.line_account,
        "facebook": $scope.custom.facebook_account,
      });

      $http.put(Routing.generate('api_auction_customer_update', {id: $scope.custom.id}), data, $scope.config).success(function (res) {
        $scope.customHandleResponse = res;
        $scope.custom = {};
        $scope.customHandleResponse.optype = 1;
      });
    };

    $scope.deleteCustom = function () {
      $http.delete(Routing.generate('api_auction_customer_delete', {id: $scope.customHandleResponse.custom.id})).success(function (res) {
        $scope.customHandleResponse = res;
        $scope.customHandleResponse.optype = 2;
      });
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

    $scope.fetchCustom = function (mobil) {
      $scope.customHandleResponse = {};
      $scope.isAu = !$scope.isAu;

      if (mobil == $scope.tmpMobil) {
        return false;
      }

      let url = Routing.generate('api_auction_custom_list') + "?mobil=" + mobil;

      $http.get(url).success(function (res) {
        $scope.custom = {
          "name": '',
          "mobil": '',
          "email": '',
          "address": '',
          "sex": '',
          "birthday": ''
        };

        if ($scope.isSuccess(res)) {
          $scope.custom = res.custom;
          $scope.custom.birthday = formatBirthday($scope.custom.birthday);
          $scope.custom.hasExist = true;
        } else {
          $scope.custom.mobil = mobil;
          $scope.custom.hasExist = false;
        }

        $scope.tmpMobil = mobil;
      });
    };

    var formatBirthday = function (birthday) {
      return birthday.substr(0, 10);
    }

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
      return Routing.generate('goods_edit_v2_from_sn', {sn: sn}) + '/#_soldop';
    };

    $scope.isInputValid = function (price, mobil) {
      return 0 < parseInt(price) && 4 < mobil.length;
    };

    $scope.isMobilEmpty = function (mobil) {
      return 0 === mobil.length;
    };

    $scope.getProfit = function (cost, perc, price) {
      return AuctionHelper.getProfit(cost, perc, price);
    };

    $scope.getSn = function (product) {
        return AuctionHelper.getExtensibleSn(product);
    };

    $scope.isCustomFormValid = function (custom) {
      return custom.mobil && custom.name && 4 < custom.mobil.length && 1 < custom.name.length;
    };
}]);
