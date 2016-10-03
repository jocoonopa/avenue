'use strict';

/* Controllers */

/**
 * Based on below three principles to edit
 *
 * 1. punchSuccessSns
 * 2. currentPunchSn
 * 3. returnObj
 */
auctionCtrl.controller('PunchInCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) {

  var callback = function (res, sn) {
    if ($scope.isActSuccess(res.status)) {
      $scope.punchSuccessSns.push(sn);

      $.unique($scope.punchSuccessSns);
    } else {
      $scope.punchFailedSns.push(sn);

      $.unique($scope.punchFailedSns);
    }

    $scope.returnObj = res;
    $scope.currentPunchSn = '';
  };

  $scope.config = {
    headers : {
      'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
    }
  };

  $scope.currentPunchSn = '';
  $scope.punchSuccessSns = [];
  $scope.punchFailedSns = [];
  $scope.returnObj = {};

  $scope.punch = function (sn) {
    var sn = sn.replace('%', '');
    var data = $.param({sn: sn});

    return $scope.request(data).success(function (res) {
      callback(res, sn);
    });
  };

  $scope.request = function (data) {
    return $http.post($scope.getUrl(), data, $scope.config);
  };

  $scope.isActSuccess = function (status) {
    return 1 === parseInt(status);
  }

  $scope.genProductUrl = function (sn) {
    return Routing.generate('goods_edit_v2_from_sn', {sn: sn});
  };

  $scope.getUrl = function () {
    return Routing.generate('api_new_auction', {_format: 'json'});
  }
}]);
