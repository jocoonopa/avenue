'use strict';

/* Services */

var exchangeRateServices = angular.module('exchangeRateServices', ['ngResource']);

exchangeRateServices.factory('ExchangeRate', ['$resource',
  function ($resource) {
  return $resource(Routing.generate('api_query_exchange_rate') + '/:id', null, 
    {
    	update: { method: 'PUT'}
    });
}]);