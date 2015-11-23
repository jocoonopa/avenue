'use strict';

/* App Module */

var myApp = angular.module('myApp', [
  'ngRoute',
  'ngAnimate',
  'ngSanitize', // for ngBindhtml
  'activityServices',
  'activityCtrl'
]);

myApp.config(['$routeProvider', '$httpProvider',
  function ($routeProvider, $httpProvider) {
  $routeProvider.
    when('/activity', {
      templateUrl: Routing.generate('activity_template_list'),
      controller: 'ActlistCtrl'
    }).
    when('/activity/:activityId', {
      templateUrl: Routing.generate('activity_template_detail'),
      controller: 'ActDetailCtrl'
    }).
    otherwise({
      redirectTo: '/activity'
    });

  $httpProvider.responseInterceptors.push('myHttpInterceptor');

  var blockUIFunction = function blockUIFunction(data, headersGetter) {
    $.blockUI({ message: null });
    $('.modal-footer button').prop('disabled', true); 
    return data;
  };

  $httpProvider.defaults.transformRequest.push(blockUIFunction);
}]);

myApp.factory('myHttpInterceptor', function ($q, $window) {
  return function (promise) {
    return promise.then(function (response) {
      $.unblockUI();
      $('.modal-footer button').prop('disabled', false); 
      return response;
    }, function (response) {
      $.unblockUI();
      $('.modal-footer button').rprop('disabled', false); 
      return $q.reject(response);
    });
  };
});