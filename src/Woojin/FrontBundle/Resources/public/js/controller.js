'use strict';

/* Controllers */

var avenueCtrls = angular.module('avenueCtrls', []);

avenueCtrls.controller('FilterProductCtrl',['$scope', '$http', '$q', 'GoodsPassport', 'Color', 'Brand', 'Pattern', 'Level', 'Material', 'Store',
    function ($scope, $http, $q, GoodsPassport, Color, Brand, Pattern, Level, Material, Store) {
    
    var isJson = function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }

        return true;
    };

    var setDefault = function () {
        var defaultBrandId = $('input[name="defaultBrand"]').val();
        var defaultPatternId = $('input[name="defaultPattern"]').val();

        angular.forEach($scope.conditionContainer.gd.brand, function (brand) {
            if (brand.id === parseInt(defaultBrandId)) {
                brand.isChecked = true;
            }
        });

        if (parseInt(defaultPatternId) === 999) {
            angular.forEach($scope.conditionContainer.gd.pattern, function (pattern) {
                var arr = [22, 41];

                if (arr.indexOf(pattern.id) !== -1) {
                    pattern.isChecked = true;
                }
            });
        } else {
            angular.forEach($scope.conditionContainer.gd.pattern, function (pattern) {
                if (pattern.id === parseInt(defaultPatternId)) {
                    pattern.isChecked = true;
                }
            });
        }

        if (angularSource.promotion) {
            angular.forEach($scope.conditionContainer.gd.promotion, function (promotion) {
                if (promotion.id === parseInt(angularSource.promotion.id)) {
                    promotion.isChecked = true;
                }
            });
        }    
    };

    var _scroll = function () {
        var body = $('html, body');

        body.animate({scrollTop: parseInt($('#category').offset().top) - 110}, '500');
    };

    $scope.conditionContainer = {
        'gd': {},
        'od': {},
        'op': {}
    };

    var hash = decodeURI(window.location.hash).substring(1);
    var pageObj = (isJson(hash)) ? JSON.parse(hash) : {};

    $scope.name = '';
    $scope.perPage = (pageObj.perPage) ? pageObj.perPage : 30;
    $scope.currentPage = (pageObj.page) ? pageObj.page : 1;
    $scope.orderBy = (pageObj.orderBy) ? pageObj.orderBy : {
        sort: 'gd.id',
        order: 'DESC'
    };

    $scope.isFirstFetch = true;
    $scope.displayNotFound = false;
    $scope.sortLabel = '預設';
    $scope.now = new Date();
    $scope.defer = null;

    var init = function () {
        var defer = $q.defer();
        var promises = [];

        $('.not-found').removeClass('hidden');
        
        $scope._rows = [];
        $scope.goodses = [];
        $scope.conditionContainer.gd.brand = JSON.parse(angularSource.brands);
        $scope.conditionContainer.gd.pattern = JSON.parse(angularSource.patterns);
        $scope.conditionContainer.gd.level = [
            {id: 23, name: '展示品'},
            {id: 22, name: '全新商品'},
            {id: 100, name: '二手商品'}
        ];

        $scope.conditionContainer.gd.promotion = JSON.parse(angularSource.promotions);
        
        if (angularSource.name) {
            $scope.name = angularSource.name;
        }

        // $scope.colorPromise = Color.query().$promise.then(function (colors) {
        //     $scope.conditionContainer.gd.color = colors;
        // });

        // promises.push($scope.colorPromise);

        $q.all(promises).then(function () {
            setDefault();
            $scope.fetch(defer);
        });
    };

    $scope.isNewArrival = function (goods) {
        var createdDate = new Date(goods.created_at);

        return (createdDate.dateDiff('d', createdDate) < 7);
    };

    $scope.generateUrl = function (goods) {
        return Routing.generate('front_product_show', {id: goods.id});
    };

    /**
     * 取得官網顯示價格
     * 
     * ======== flow =========
     *
     * 如果不存在活動，返回 null
     * 
     * 如果不允許網路顯示，直接返回 null
     *
     * 如果網路售價合法[大於等於 100，且小於原始售價]
     *     返回網路售價
     *
     * 存在活動且活動折扣 < 1
     *     返回原始售價 * 活動折扣金額
     *
     * 返回 null
     * 
     * ======= End Flow =======
     *     
     * @return integer | boolean
     */
    $scope.promotionPrice = function (goods) {
        return window.getPromotionPrice(goods, true);
    };

    $scope.getBadgeColor = function (count) {
        if (count <= 10) {
            return 'badge-light';
        } else if(count <= 20) {
            return 'badge-green';
        } else if(count <= 40) {
            return 'badge-blue';
        } else if(count <= 70) {
            return 'badge-purple';
        } else if(count <= 110) {
            return 'badge-yellow';
        } else if(count <= 160) {
            return 'badge-orange';
        } 
    };

    var getPostCondition = function () {
        var postCondition = {};

        for (var key in $scope.conditionContainer) {//'entity'
            var entitys = $scope.conditionContainer[key];
            
            for (var _key in entitys) {// attributes
                var entity = entitys[_key];
                
                for (var __key in entity) {// each attribute
                    var attribute = entity[__key];

                    if (attribute.isChecked) {// 如果有勾選...
                        if (!postCondition[key]) {
                            postCondition[key] = {};
                        }

                        if (!postCondition[key][_key]) {
                            postCondition[key][_key] = [];
                        }

                        postCondition[key][_key].push(attribute);
                    }
                }
            }
        }

        if ($scope.name.length >= 1) {
            postCondition.name = $scope.name;
        }

        if (angularSource.category) {
            if (!postCondition['gd']) {
                postCondition.gd = {};
            }

            postCondition.gd.category = angularSource.category;
        }
        
        postCondition.upperPrice = parseInt($('.slider-snap-value-upper').text());
        postCondition.lowerPrice = parseInt($('.slider-snap-value-lower').text());
        postCondition.perPage = $scope.perPage;
        postCondition.page = $scope.currentPage;

        var hash = decodeURI(window.location.hash).substring(1);

        postCondition.orderBy = $scope.orderBy;
        
        return postCondition;
    };

    $scope.myconsole = function (str) {
        console.log(str);
    };

    $scope.seoName = function (goods) {
        return getSeoName(goods);
    };

    $scope.transLevel = function (goods) {
        if (goods.level) {
            switch (goods.level.id)
            {
                case 22:
                    return goods.level.name;

                    break;
                case 23:
                    return goods.level.name;

                    break;
                default:
                    return '二手商品';

                    break;
            }
        }

        return '未區分';
    };

    $scope.promotionRemainDays = function (promotion) {
        if (!promotion) {
            return false;
        }

        var startAt =  new Date(promotion.start_at);
        if (parseInt($scope.now.dateDiff('d', startAt)) > 0) {
            return false;
        }
        
        var stopAt = new Date(promotion.stop_at);

        return parseInt($scope.now.dateDiff('d', stopAt));
    };

    $scope.pagerFetch = function () {
        if ($scope.isFirstFetch) {
            $scope.perPage = pageObj.perPage;
            $scope.currentPage = pageObj.page;
            $scope.orderBy = pageObj.orderBy;
        } else {
            $scope.fetch();
        }
    };

    $scope.fetch = function (defer) {
        var hash = decodeURI(window.location.hash).substring(1);
        $scope.displayNotFound = false;
        $scope.defer = defer || false;
        $scope.goodses = [];
        $scope._rows = [];

        if ($scope.isFirstFetch) {
            if ((hash.length > 0) && isJson(hash)) {  
                checkFromHash(JSON.parse(hash));

                if (window.location.hash === encodeURI('#' + JSON.stringify(getPostCondition()))) {
                    return fetching();
                }
            }  
        }

        $scope.isFirstFetch = false;

        return window.location.hash = encodeURI('#' + JSON.stringify(getPostCondition()));
    };

    var fetching = function () {
        _scroll();

        $scope.goodsPromise = $http({
            url: Routing.generate('api_goodsPassport_fetchWithCondition'),
            data : {
                jsonCondition: decodeURI(window.location.hash).substring(1)
            },
            method : 'POST',
            headers : {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}
        })
        .success(function (res) {
            $scope._rows = [];
            $scope.goodses = [];
            $scope.totalItems = res.count;
            $scope.isFirstFetch = false;

            // 如果回傳的商品陣列為空，但統計數字卻大於0, 且目前頁數不為1，則自動把頁數設定為1再執行一次 fetch
            if ((res.count > 0) && (res.goodses.length === 0) && ($scope.currnetPage > 1)) {
                $scope.currnetPage = 1;
                return fetching();
            }

            angular.forEach(res.goodses, function (goods, i) {
                if (!$scope._rows[Math.floor(i/3)]) {
                    $scope._rows[Math.floor(i/3)] = [];
                }

                $scope._rows[Math.floor(i/3)].push(goods);
            });

            angular.forEach($scope._rows, function (row) {
                this.push(row);
            }, $scope.goodses);

            if ($scope.defer) {
                $scope.defer.resolve();
            }

            if (res.count == 0) {
                $scope.displayNotFound = true;
            } else {
                window.ac.reflect();
            }
        });
    }

    $scope.resetCondition = function () {
        for (var key in $scope.conditionContainer) {//'entity'
            var entitys = $scope.conditionContainer[key];
            
            for (var _key in entitys) {// attributes
                var entity = entitys[_key];
                
                for (var __key in entity) {// each attribute
                    var attribute = entity[__key];

                    if (attribute.isChecked) {
                        attribute.isChecked = false;
                    }
                }
            }
        }

        $scope.name = '';
    };

    $scope.allowAddToCart = function (product) {
        return (product.status.id == 1 || product.status.id == 6);
    };

    $scope.allowBehalf = function (product) {
        return ((product.status.id == 7 || product.status.id == 2) && product.is_behalf == 1);
    };

    $scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
        window.awl.init();
    });

    var checkFromHash = function (pageObj) {
        var list = ['level', 'promotion', 'brand', 'pattern'];

        if (pageObj.gd) {
            for (var i in list) {
                for (var j in $scope.conditionContainer.gd[list[i]]) {
                    $scope.conditionContainer.gd[list[i]][j].isChecked = false;
                }

                if (pageObj.gd[list[i]]) {
                    for (var j in pageObj.gd[list[i]]) {
                        for (var k in pageObj.gd[list[i]]) {
                            for (var l in $scope.conditionContainer.gd[list[i]]) {
                                if ($scope.conditionContainer.gd[list[i]][l].isChecked) {
                                    continue;
                                }

                                $scope.conditionContainer.gd[list[i]][l].isChecked = (pageObj.gd[list[i]][k] && pageObj.gd[list[i]][k].id === $scope.conditionContainer.gd[list[i]][l].id)
;
                            }
                        }
                    }
                }
            }
        }
        
        $scope.perPage = pageObj.perPage;
        $scope.currentPage = pageObj.page;
        $scope.orderBy = pageObj.orderBy;
    };

    $(window).on('hashchange', function() {
        var hash = decodeURI(window.location.hash).substring(1);
        var pageObj = JSON.parse(hash);
        
        checkFromHash(pageObj);
        $scope.isFirstFetch = false;

        fetching();
    });

    init();
}]);