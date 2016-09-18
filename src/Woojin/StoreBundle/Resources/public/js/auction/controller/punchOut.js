'use strict';

/* Controllers */

/**
 * Based on below three principles to edit
 * 
 * 1. punchOutSuccessSns
 * 2. currentPunchOutSn
 * 3. returnObj
 */
auctionCtrl.controller('PunchOutCtrl', ['$scope', '$routeParams', '$http', '$controller',
  function ($scope, $routeParams, $http, $controller) { 

  $controller('PunchInCtrl', {$scope: $scope});

  $scope.getUrl = function () {
    return Routing.generate('api_back_auction', {_format: 'json'});
  }

  $scope.request = function (data) {
    return $http.put($scope.getUrl(), data, $scope.config);
  };
}]);