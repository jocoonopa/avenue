'use strict';

/* App Module */

var myApp = angular.module('myApp', [
  'ngRoute',
  'ngAnimate',
  'fixcnCtrl'
]);

myApp.config(['$routeProvider', '$httpProvider',
  function ($routeProvider, $httpProvider) {
  
  $httpProvider.responseInterceptors.push('myHttpInterceptor');

  var blockUIFunction = function blockUIFunction(data, headersGetter) {
    var oCss = { 
      'border' : 'none',
      'padding' : '15px',
      'backgroundColor' : '#000',
      '-webkit-border-radius' : '10px',
      '-moz-border-radius' : '10px',
      'opacity' : .5,
      'color' : '#fff'
    };
    $.blockUI({ css: oCss }); 

    return data;
  };

  $httpProvider.defaults.transformRequest.push(blockUIFunction);
}]);

myApp.factory('myHttpInterceptor', function ($q, $window) {
  return function (promise) {
    return promise.then(function (response) {
      $.unblockUI();
      return response;
    }, function (response) {
      $.unblockUI();
      return $q.reject(response);
    });
  };
});

/* Controllers */

var fixcnCtrl = angular.module('fixcnCtrl', []);

fixcnCtrl.controller( 'fixcnCtrl', ['$scope', '$routeParams', '$http',
  function ($scope, $routeParams, $http) {
    $http.get(Routing.generate('goods_api_cn_nox_fixed') ).success(function (data) {
      $scope.goods = data;
    });

    $scope.Math = window.Math;

    $scope.save = function (index, good) {

      if ($scope.tmp_price === good.goods_price || $scope.tmp_cost === good.goods_cost ) {
        return;
      }

      $http.put(Routing.generate('api_update_goods', { id: good.goods_passport_id }), good)
        .success(function (good) {
          //$scope.goods[index] = good;
        }).error(function () {
          alert('error');
        });
    }

    $scope.pushTmp = function (num, type) {
      if (type === 0) {
        $scope.tmp_cost = num;
        $scope.tmp_price = 0;
      }
      
      if (type === 1) {
        $scope.tmp_price = num;
        $scope.tmp_cost = 0;
      }
      
    }
}]);




