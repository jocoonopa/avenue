'use strict';

/* App Module */

var promotionApp = angular.module('promotionApp', [
  'ngRoute',
  'ngAnimate',
  'PromotionControllers'
]);

promotionApp.config(['$routeProvider', '$httpProvider',
  function ($routeProvider, $httpProvider) {

  // Use x-www-form-urlencoded Content-Type
  $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

  /**
   * The workhorse; converts an object to x-www-form-urlencoded serialization.
   * @param {Object} obj
   * @return {String}
   */ 
  var param = function(obj) {
    var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

    for(name in obj) {
      value = obj[name];

      if(value instanceof Array) {
        for(i=0; i<value.length; ++i) {
          subValue = value[i];
          fullSubName = name + '[' + i + ']';
          innerObj = {};
          innerObj[fullSubName] = subValue;
          query += param(innerObj) + '&';
        }
      }
      else if(value instanceof Object) {
        for(subName in value) {
          subValue = value[subName];
          fullSubName = name + '[' + subName + ']';
          innerObj = {};
          innerObj[fullSubName] = subValue;
          query += param(innerObj) + '&';
        }
      }
      else if(value !== undefined && value !== null)
        query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
    }

    return query.length ? query.substr(0, query.length - 1) : query;
  };

  // Override $http service's default transformRequest
  $httpProvider.defaults.transformRequest = [function(data) {
    return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
  }];

  $httpProvider.responseInterceptors.push('myHttpInterceptor');
  var oCss = { 
      'border' : 'none',
      'padding' : '15px',
      'backgroundColor' : '#000',
      '-webkit-border-radius' : '10px',
      '-moz-border-radius' : '10px',
      'opacity' : .5,
      'color' : '#fff'
    };

  var blockUIFunction = function blockUIFunction(data, headersGetter) {
    $.blockUI({ 
      css: oCss
    });

    $('.modal-footer button').prop('disabled', true); 
    return data;
  };

  $httpProvider.defaults.transformRequest.push(blockUIFunction);
}]);

promotionApp.factory('myHttpInterceptor', function ($q, $window) {
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

var PromotionControllers = angular.module('PromotionControllers', []);

PromotionControllers.controller('PromotionCtrl', ['$scope', '$http', function ($scope, $http) {
  var init = function () {
    $scope.promotionId = $('#promotion-relate').data('id');
    getProductsInPromotion();
    getProductsSoldInPromotion();
  };

  $scope.checkAll = function (products) {
    angular.forEach(products, function (product) {
      product.isChecked = !product.isChecked;
    });
  };

  $scope.search = function () {
    var post = $('form').serializeJSON();

    $http.post(Routing.generate('promotion_api_fetch'), post)
    .success(function (products) {
      $('#myTab').find('li a').eq(0).click();
      $scope.resProducts = products;  
    })
    .error(function () {

    });
  };

  $scope.remove = function (index) {
    $scope.promotionProducts[index].isChecked = true;

    removeProcess([$scope.promotionProducts[index].id]);
  };

  $scope.batchRemove = function () {
    removeProcess(collectIds('promotionProducts'));
  };

  var removeProcess = function (ids) {
    if (ids.length === 0) {
      return false;
    }

    $http.put(Routing.generate('promotion_api_product_delete', {id: $scope.promotionId}), {ids: JSON.stringify(ids)})
      .success(function () {
        var tmp = [];

        angular.forEach($scope.promotionProducts, function (product, index) {
          if (!product.isChecked) {
            this.push(product);
          }
        }, tmp);

        $scope.promotionProducts = tmp;
      });
  };

  $scope.add = function (index) {
    $scope.resProducts[index].isChecked = true;

    addProcess([$scope.resProducts[index].id]);
  }

  $scope.batchAdd = function () {
    addProcess(collectIds('resProducts'));
  };

  $scope.refresh = function () {
    getProductsInPromotion();
  };

  $scope.getProductUrl = function (product) {
    return Routing.generate('goods_edit_v2', {id: product.id});
  };

  var collectIds = function (name) {
    var ids = [];

    angular.forEach($scope[name], function (product) {
      if (product.isChecked) {
         this.push(product.id);
      }
    }, ids);

    return ids;
  };

  var addProcess = function (ids) {
    if (ids.length === 0) {
      return false;
    }
    
    $http.post(Routing.generate('promotion_api_product_add', {id: $scope.promotionId}), {ids: JSON.stringify(ids)})
      .success(function () {
        var tmp = [];

        angular.forEach($scope.resProducts, function (product, index) {
          if (!product.isChecked) {
            this.push(product);            
          } else {
            $scope.promotionProducts.push(product);
          }
        }, tmp);

        $scope.resProducts = tmp;
      });
  };

  var getProductsInPromotion = function () {
    var post = $('form').serializeJSON();
    post.promotion = $scope.promotionId;

    $http.post(Routing.generate('promotion_api_fetch'), post)
    .success(function (products) {
      $scope.promotionProducts = products;
    })
    .error(function () {

    });
  };

  var getProductsSoldInPromotion = function () {
    $http.get(Routing.generate('promotion_api_fetch_sold', {id: $scope.promotionId}))
      .success(function (products) {
        $scope.soldProducts = products;
      })
      .error(function () {

      });
  };
  
  init();
}]);





