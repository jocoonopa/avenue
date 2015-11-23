'use strict';

/* Directives */

mobileQueryApp.directive('jqmDetect', function () {
  return function link (scope, element, attrs) {
    /**
     * 刷新送出的條件值( 因為混用jqm 的關係, 所以要多這一步驟)
     */
    var refreshCondition = function () {
      scope.condition.storeSn      = $('select[name="storeSn"]').val();
      scope.condition.brand        = $('select[name="brand"]').val();
      scope.condition.goodsStatus  = $('select[name="goodsStatus"]').val();
      scope.condition.goodsLevel   = $('select[name="goodsLevel"]').val();
      scope.condition.activity     = $('select[name="activity"]').val();

      return scope;
    };

    /**
     * 重新初始化 modile 頁面，因為商品list會重新render的關係，這邊的jq 初始動作
     * 必須由我們手動處理
     */
    var jqReInit = function () {
      setTimeout(function () {
        $(element).page('destroy').page();
        $('h4.ui-collapsible-heading').not('.ui-collapsible-heading-collapsed').click();
        $('#right-menu').panel('close');
      }, 1000); 
    };

    /**
     * 重設查詢條件
     */
    scope.resetCondition = function () {
      // jqm 的 select 有點麻煩，不太確定怎們直接改value就先這樣寫了
      $('select').find('option').prop('selected', false).end().selectmenu('refresh', true);

      scope.condition.goodsName     = '';
      scope.condition.goodsSn       = '';
      scope.condition.storeSn       = [];
      scope.condition.goodsStatus   = [];
      scope.condition.goodsLevel    = [];
      scope.condition.brand         = [];
      scope.condition.activity      = [];
    };

    /**
     * jqm detect 頁面的取得資料流程
     */
    scope.customQueryProcess = function () {
      // 刷新查詢條件
      refreshCondition().getQueryResult();
    };

    // 監聽 goods 的變化, 若數量>0, 則重新初始化頁面
    scope.$watch( 'goods', function (value) {
      return (value.length === 0) ? false : jqReInit();
    });

    // 如果是由multi select 對話框導向來的，則觸發按下搜尋條件按鈕動作，
    // 避免使用時還要重新按搜尋條件
    $(element).on('pagebeforeshow', function(event, data) {
      return (data.prevPage.context) ? $('#query-condition').click() : false;
    });
  };
});

mobileQueryApp.directive('jqmList', [function () {
  return {
    restrict: 'E',
    templateUrl: '/bundles/woojingoods/template/mobileGoodsList.html'
  };
}])