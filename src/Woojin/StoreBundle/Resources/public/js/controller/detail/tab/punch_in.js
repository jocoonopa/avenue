'use strict';

/* Controllers */

activityCtrl.controller( 'PunchInCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) { 

  $scope.punchInBarcode           = '';
  $scope.punchInBarcodes          = [];
  $scope.successPunchInBarcodes   = [];
  $scope.failPunchInBarcodes      = [];
  
  /**
   * 新增條碼到刷入清單
   * 
   * @return void
   */
  $scope.addPunchInList = function () {
    if ($scope.punchInBarcode.length > 0) {
      $scope.punchInBarcodes.push({ sn: $scope.punchInBarcode });
      $scope.punchInBarcode = '';
    }
  }; 

  /**
   * 移除刷入清單上的某項
   * 
   * @param  {integer} index [索引]
   * @return void
   */
  $scope.removePunchInList = function (index) {
    $scope.punchInBarcodes.splice(index, 1);
  };

  /**
   * 刷入活動
   *
   * @return void
   */
  $scope.savePunchInList = function () {
    $http.put(Routing.generate('api_punch_in_update_activity', { id: $routeParams.activityId }), $scope.punchInBarcodes)
    .success(function (data) {
      $scope.successPunchInBarcodes   = data.success;
      $scope.failPunchInBarcodes      = data.fail;
      $scope.punchInBarcodes          = [];
      $scope.getActivityGoods();
    });
  };

  /**
   * 檢查有無刷入成功的條碼
   * 
   * @return {Boolean}
   */
  $scope.hasSuccessPunchIn = function () {
    return ($scope.successPunchInBarcodes.length > 0);
  };

  /**
   * 清空刷入成功條碼的陣列
   * 
   * @return {array} [Should be empty]
   */
  $scope.emptySuccessPunchInBarcodes = function (index) {
    return (typeof index !== 'number') ? $scope.successPunchInBarcodes = [] : $scope.successPunchInBarcodes.splice(index, 1);
  }

  /**
   * 檢查有無刷入失敗的條碼
   * 
   * @return {Boolean}
   */
  $scope.hasFailPunchIn = function () {
    return ($scope.failPunchInBarcodes.length > 0);
  }

  /**
   * 清空刷入失敗條碼的陣列
   * 
   * @return {array} [Should be empty]
   */
  $scope.emptyFailPunchInBarcodes = function (index) {
    return (typeof index !== 'number') ? $scope.failPunchInBarcodes = [] : $scope.failPunchInBarcodes.splice(index, 1);
  }

}]);