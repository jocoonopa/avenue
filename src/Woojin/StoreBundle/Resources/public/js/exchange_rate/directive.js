'use strict';

/* Directives */

myApp.directive('jqNumeric', function () {
  return function (scope, element) {
    element.numeric();
  }
});