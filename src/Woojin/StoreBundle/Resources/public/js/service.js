'use strict';

/* Services */

var activityServices = angular.module('activityServices', ['ngResource']);

activityServices.factory('Activity', ['$resource',
  function ($resource) {
  return $resource(Routing.generate('actlist') + '/:activityId', null, 
    {
    	update: { method: 'PUT'}
    });
}]);

var auctionServices = angular.module('auctionServices', ['ngResource']);

auctionServices.factory('Auction', ['$resource',
  function ($resource) {
  return $resource(Routing.generate('api_auction_list') + '/:auctionId', null, 
    {
        update: { method: 'PUT'}
    });
}]);