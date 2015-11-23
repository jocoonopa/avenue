'use strict';

/* Controller */

var mobileControllers = angular.module('mobileControllers', []);

mobileControllers.controller('mobileQueryCtrl', ['$scope', '$routeParams', '$http', 'Goods',
	function ($scope, $routeParams, $http, Goods) {
		
	/**
	 * 根據搜尋條件取得查詢的商品資料
	 */
	$scope.getQueryResult = function () {
		// 透過api取得商品資料, 取得資料儲存在 goods
		Goods.query({ condition: JSON.stringify( $scope.condition ) })
			.$promise.then(function (goods) {
          $scope.goods 				= goods;
          $scope.goodsDetail 	= [];
        });
	};

	/**
   * 根據產品索引取得產品詳細資料
   * 
   * @param  {integer} goodsId
   * @return {void}         
   */
  $scope.getGoodsDetail = function (goodsId, index) {
  	if (!goodsId) {
  		return;
  	}

  	if (typeof $scope.goodsDetail[index] !== 'undefined') {
  		return;
  	}

    $http.get(Routing.generate( 'api_get_goods_detail', { id: goodsId }))
    	.success(function (data) {
	      $scope.goodsDetail[index] = data;
	    });
  };

  $scope.goodsDetail 						 = [];
	$scope.goods 									 = [];
	$scope.condition 							 = {};
	$scope.condition.goodsName     = '';
  $scope.condition.goodsSn       = '';
  $scope.condition.brandSn       = '';
  $scope.condition.storeSn       = [];
  $scope.condition.goodsStatus   = [];
  $scope.condition.goodsLevel    = [];
  $scope.condition.brand         = [];
  $scope.condition.activity 		 = [];

}]);