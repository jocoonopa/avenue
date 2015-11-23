'use strict';

/* Directives */

myApp.directive('ngEnter', function () {
  return function (scope, element, attrs) {
    element.bind('keydown keypress', function (event) {
      if(event.which === 13) {
        scope.$apply(function (){
          scope.$eval(attrs.ngEnter);
        });

        event.preventDefault();
      }
    });
  };
});

myApp.directive('ngDatepicker', function () {
  return function (scope, element) {
    element.datepicker({
      format: 'yyyy-mm-dd',
      todayBtn: 'linked',
      language: 'zh-TW',
      todayHighlight: true
    });
  }
});

myApp.directive('ngTab', function () {
  return function (scope, element) {
    element.click(function (e) {
      e.preventDefault();
      $(this).tab('show');
    }); 
  }
});

myApp.directive('ngStatusColor', function () {
  return function (scope, element) { 
    if ( scope.goods.status === '售出') {
      element.parent().addClass('active');
    }
  }
});