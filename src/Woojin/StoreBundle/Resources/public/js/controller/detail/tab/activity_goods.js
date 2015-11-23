'use strict';

/* Controllers */

activityCtrl.controller( 'ActivityGoodsCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) { 

  $scope.query      = '';
  $scope.orderProp  = 'sn';
  $scope.sellNum    = '';
  $scope.getActivityGoods(); // declare @ detail_ctrl.js

}]);