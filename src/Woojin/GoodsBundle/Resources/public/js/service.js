'use strict';

/* Services */

var mobileServices = angular.module('mobileServices', ['ngResource']);

mobileServices.factory('Goods', ['$resource',
  function ($resource) {
  	return $resource(Routing.generate('goods_api_query') + '/:condition');
}]);