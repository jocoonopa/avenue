'use strict';

/* Controllers */

activityCtrl.controller( 'ActDetailCtrl', ['$scope', '$routeParams', '$http', 'Activity',
	function ($scope, $routeParams, $http, Activity) { 
  
  $scope.activityGoods  = [];
  $scope.goodsDetail    = '';
  $scope.activity       = Activity.get({ activityId: $routeParams.activityId });
  
  /**
   * 根據產品索引取得產品詳細資料
   * 
   * @param  {integer} goodsId
   * @return {void}         
   */
  $scope.getGoodsDetail = function (goodsId) {
    $http.get(
      Routing.generate( 'api_get_goods_detail', { id: goodsId })
    ).success(function (data) {
      $scope.goodsDetail = data;
    });
  }

  /**
   * 清除 scope 的產品資料
   * 
   * @return {void}
   */
  $scope.cleanGoodsDetail = function () {
    $scope.goodsDetail = '';
  };

  /**
   * 取得屬於該的所有活動產品
   * 
   * @return {void}
   */
  $scope.getActivityGoods = function () {
    $http.get( Routing.generate('api_goods_in_activity', { id: $routeParams.activityId }))
      .success(function (data) {
        $scope.activityGoods = data;
      });
  };

}]);