'use strict';

/* Controllers */

activityCtrl.controller( 'PunchOutCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) { 

  $scope.punchOutBarcode          = '';
  $scope.punchOutBarcodes         = [];
  $scope.successPunchOutBarcodes  = [];
  $scope.failPunchOutBarcodes     = [];
  
  /**
   * 新增條碼到刷出清單
   * 
   * @return void
   */
  $scope.addPunchOutList = function () {
    if ($scope.punchOutBarcode.length > 0) {
      $scope.punchOutBarcodes.push({ sn: $scope.punchOutBarcode });
      $scope.punchOutBarcode = '';
    }
  }; 

  /**
   * 移除刷出清單上的某項
   * 
   * @param  {integer} index [索引]
   * @return void
   */
  $scope.removePunchOutList = function (index) {
    $scope.punchOutBarcodes.splice(index, 1);
  };

  /**
   * 刷回店內
   *
   * @return void
   */
  $scope.savePunchOutList = function () {
    $http.put(Routing.generate('api_punch_out_update_activity', { id: $routeParams.activityId }), $scope.punchOutBarcodes)
    .success(function (data) {
      $scope.successPunchOutBarcodes  = data.success;
      $scope.failPunchOutBarcodes     = data.fail;
      $scope.punchOutBarcodes         = [];
      $scope.getActivityGoods();
    });
  };

  /**
   * 檢查有無刷出成功的條碼
   * 
   * @return {Boolean}
   */
  $scope.hasSuccessPunchOut = function () {
    return ($scope.successPunchOutBarcodes.length > 0);
  };

  /**
   * 清空刷出成功條碼的陣列
   * 
   * @return {array} [Should be empty]
   */
  $scope.emptySuccessPunchOutBarcodes = function (index) {
    return (typeof index !== 'number') ? $scope.successPunchOutBarcodes = [] : $scope.successPunchOutBarcodes.splice(index, 1);
  }

  /**
   * 檢查有無刷出失敗的條碼
   * 
   * @return {Boolean}
   */
  $scope.hasFailPunchOut = function () {
    return ($scope.failPunchOutBarcodes.length > 0);
  }

  /**
   * 清空刷出失敗條碼的陣列
   * 
   * @return {array} [Should be empty]
   */
  $scope.emptyFailPunchOutBarcodes = function (index) {
    return (typeof index !== 'number') ? $scope.failPunchOutBarcodes = [] : $scope.failPunchOutBarcodes.splice(index, 1);
  }

}]);