'use strict';

/* App Module */

var myApp = angular.module('myApp', [
  'ngRoute',
  'ngAnimate',
  'ngSanitize', // for ngBindhtml
  'ui.bootstrap',
  'activityServices',
  'activityCtrl',
  'auctionCtrl',
  'AuctionHelper',
  'UserAuthHelper'
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
    when('/auction', {
      templateUrl: Routing.generate('auction_template_list'),
      controller: 'AuctionPunchCtrl'
    }).
    when('/auction_sold', {
        templateUrl: Routing.generate('auction_template_sold'),
        controller: 'AuctionSoldCtrl'
    }).
    when('/auction_profit', {
        templateUrl: Routing.generate('auction_template_profit'),
        controller: 'AuctionProfitCtrl'
    }).
    otherwise({
      redirectTo: '/activity'
    });

  $httpProvider.responseInterceptors.push('myHttpInterceptor');

  var blockUIFunction = function blockUIFunction(data, headersGetter) {
    if (true === window.noblockUI) {
      return data;
    }

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
