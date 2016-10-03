'use strict';

/* Services */

angular.module('myApp').constant('version', 1);

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

var AuctionHelper = angular.module('AuctionHelper', ['ngResource']);
AuctionHelper.service('AuctionHelper', function () {
    var self = this;

    self.getProfit = function (cost, perc, price) {
      return isNaN(price) ? '' : Math.floor(perc * (price - cost));
    };
});
