'use strict';

/* App Module */

var mobileQueryApp = angular.module('mobileQueryApp', [
  'ngRoute',
  'mobileServices',
  'mobileControllers'
]);

mobileQueryApp.config(['$routeProvider', '$httpProvider',
  function ($routeProvider, $httpProvider) {
  
  $httpProvider.responseInterceptors.push('myHttpInterceptor');

  var blockUIFunction = function blockUIFunction(data, headersGetter) {
    $.blockUI({ message: null });
    return data;
  };

  $httpProvider.defaults.transformRequest.push(blockUIFunction);
}]);

mobileQueryApp.factory('myHttpInterceptor', function ($q, $window) {
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