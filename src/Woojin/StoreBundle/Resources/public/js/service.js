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