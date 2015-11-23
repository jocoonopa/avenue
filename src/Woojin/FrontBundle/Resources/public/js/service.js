'use strict';

/* Services */

var avenueServices = angular.module('avenueServices', ['ngResource']);

avenueServices.factory('Brand', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_brand_list') + '/:id', null, {});
}]);

avenueServices.factory('GoodsPassport', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_brand_list') + '/:id', null, {
        'fetch': {
            url: Routing.generate('api_goodsPassport_fetchWithCondition'),
            method: 'GET',
            isArray: true
        }
    });
}]);

avenueServices.factory('Pattern', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_pattern_list') + '/:id', null, {});
}]);

avenueServices.factory('Color', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_color_list') + '/:id', null, {});
}]);

avenueServices.factory('Level', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_level_list') + '/:id', null, {});
}]);

avenueServices.factory('Material', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_mt_list') + '/:id', null, {});
}]);

avenueServices.factory('Store', ['$resource',function ($resource) {
    return $resource(Routing.generate('api_store_list') + '/:id', null, {});
}]);