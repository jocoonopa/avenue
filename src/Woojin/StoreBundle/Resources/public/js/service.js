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

    self.getExtensibleSn = function (product) {
      if ('undefined' === typeof product) {
        return '';
      }

      return true === product.is_allow_auction && 0 === product.cost ? product.sn + '%' : product.sn;
    };
});

var UserAuthHelper = angular.module('UserAuthHelper', ['ngResource']);
UserAuthHelper.service('UserAuthHelper', ['$http', function ($http) {
    this.getRolelist = function () {
        var promise;
        
        return !promise ? $http.get(Routing.generate('api_user_rolelist')).then(function (response) {
            return response.data;
        }) : promise;
    };

    this.hasAuth = function (rolelist, authName) {
        return 1 === parseInt(rolelist[authName]);
    };
}]);
