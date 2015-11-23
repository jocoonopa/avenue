'use strict';

/* Controllers */

var exchangeRateCtrl = angular.module('exchangeRateCtrl', []);

exchangeRateCtrl.controller( 'exchangeRateCtrl', ['$scope', '$cookies', 'ExchangeRate',
  function ($scope, $cookies, ExchangeRate) {

  $scope.exchangeRates = ExchangeRate.query();
 	
 	$scope.notTW = function (id) {
 		return (id !== 1);
 	};

 	$scope.update = function (exchange) {
 		ExchangeRate.update( 
 			{ id: exchange.id }, 
 			exchange, 
 			function (res){
 				$scope.exchangeRates = ExchangeRate.query();
 		});
 	};

}]);