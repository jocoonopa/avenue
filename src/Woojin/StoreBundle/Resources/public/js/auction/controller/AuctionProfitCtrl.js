'use strict';

/* Controllers */

auctionCtrl.controller('AuctionProfitCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) {
    document.title = '競拍毛利檢視';

    $scope.config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    };

    $scope.isAu = true;
    $scope.reverse = true;
    $scope.propertyName = 'create_at';
    $scope.auctions = [];
    $scope.stores = [{id: 0, name: ''}];
    $scope.auction_statuses = [
        {id: 0, name: '待競拍'},
        {id: 1, name: '已售出'},
        {id: 10, name: '歸還門市'}
    ];
    $scope.criteria = {
        stores: [],
        auction_statuses: [],
        buyer_mobil: '',
        seller_mobil: '',
        bsser_username: '',
        create_at: {start: '', end: ''},
        sold_at: {start: '', end: ''}
    };

    $scope.init = function () {
        $http.get(Routing.generate('api_store_valid_list')).success(function (res) {
            var stores = [];

            for (var i = 0; i < res.length; i ++) {
                var store = res[i];

                stores.push({
                    id: store.id,
                    name: store.name
                });
            }
            $scope.stores = stores;
        });
    };

    $scope.download = function () {
        var postData = genPostData();

        $('input[name="stores_str"]').val(postData.stores.join());
        $('input[name="auction_statuses_str"]').val(postData.auction_statuses.join());

        $('form').attr('action', Routing.generate('auction_export_profit')).submit();
    };

    $scope.printProductName = function (name) {
        return 8 < name.length ? name.substr(0, 8) + '...' : name;
    };

    $scope.getProfit = function (perc, price) {
      return isNaN(price) ? '' : Math.floor(perc * price);
    };

    $scope.sortBy = function(propertyName) {
        $scope.reverse = ($scope.propertyName === propertyName) ? !$scope.reverse : false;
        $scope.propertyName = propertyName;
    };

    $scope.checkAllStores = function() {
        $scope.criteria.stores = angular.copy($scope.stores);
    };
    $scope.uncheckAllStores = function() {
        $scope.criteria.stores = [];
    };

    $scope.checkAllStatus = function() {
        $scope.criteria.auction_statuses = angular.copy($scope.auction_statuses);
    };
    $scope.uncheckAllStatus = function() {
        $scope.criteria.auction_statuses = [];
    };

    $scope.submit = function () {
        $scope.isAu = !$scope.isAu;

        $http.post(Routing.generate('api_list_filter_auction'), $.param(genPostData()), $scope.config)
        .success(function (res) {
            for (var i = 0; i < res.length; i ++) {
                res[i].customProfit = $scope.getProfit(res[i].custom_percentage, res[i].price);
                res[i].storeProfit = $scope.getProfit(res[i].store_percentage, res[i].price);
                res[i].bsoProfit = $scope.getProfit(res[i].bso_percentage, res[i].price);
            }

            $scope.auctions = res;
        });
    };

    $scope.reset = function () {
        $scope.criteria = {
            stores: [],
            auction_statuses: [],
            buyer_mobil: '',
            seller_mobil: '',
            bsser_username: '',
            create_at: {start: '', end: ''},
            sold_at: {start: '', end: ''}
        };
    };

    $scope.genProductUrl = function (sn) {
      return Routing.generate('goods_edit_v2_from_sn', {sn: sn});
    };

    var genPostData = function () {
        var post = angular.copy($scope.criteria);

        post.stores = [];
        post.auction_statuses = [];

        for (var i = 0; i < $scope.criteria.stores.length; i ++) {
            post.stores.push($scope.criteria.stores[i].id);
        }

        var statuses = angular.copy($scope.criteria.auction_statuses);

        for (var i = 0; i < $scope.criteria.auction_statuses.length; i ++) {
            post.auction_statuses.push($scope.criteria.auction_statuses[i].id);
        }

        return post;
    };
}]);
