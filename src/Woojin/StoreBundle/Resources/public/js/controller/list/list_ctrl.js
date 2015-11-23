'use strict';

/* Controllers */

activityCtrl.controller( 'ActlistCtrl', ['$scope', 'Activity',
  function ($scope, Activity) {

  $scope.activitys    = Activity.query();
  $scope.successMsg   = '';
  $scope.orphanMsg    = '';
  $scope.errorMsgs    = [];
  $scope.orderProp    = 'startAt';
  $scope.reverse      = true;
  $scope.modalTitle   = '';
  $scope.method       = '';
  $scope.theActivity  = {
    id: '',
    name: '',
    description: '',
    startAt: '',
    endAt: ''
  };

  /**
   * 初始化新增活動的 modal
   *
   * @return {void} 
   */
  $scope.add = function () {
    $scope.modalTitle   = '新增活動:';
    $scope.method       = 'POST';
    $scope.clean(); 
    $scope.emptyErrorMsg();
  };

  /**
   * 初始化編輯活動的 modal
   * 
   * @param  {[integer]} id [活動索引]
   * @return { void }
   */
  $scope.edit = function (id) {
    $scope.modalTitle   = '編輯活動:';
    $scope.method       = 'UPDATE';
    Activity.get({ activityId: id })
      .$promise.then(function (activity) {
        $scope.theActivity              = activity;
        $scope.theActivity.description  = $scope.theActivity.description.replace('<br>', "\n");
      });
  };

  /**
   * 儲存 view 的資訊進資料庫 
   * @return {void} 
   */
  $scope.save = function () {
    $scope.validateForm();

    if ( !$scope.hasError ) {
      if ($scope.method === 'POST') {
        $scope.create();
      }

      if ($scope.method === 'UPDATE') {
        $scope.update();
      }
    }
  };

  /**
   * 新增活動進資料庫
   * 
   * @return {void}
   */
  $scope.create = function () {
    Activity.save( $scope.theActivity, function (res) {
      Activity.get({ activityId: res.id })
        .$promise.then(function (activity) {
          $scope.successMsg   = activity.name + '新增完成!';
          $scope.activitys    = Activity.query();
        });
    });
  };

  /**
   * 更新資料庫的活動資訊
   * 
   * @return {void} 
   */
  $scope.update = function () {
    Activity.update({ activityId: $scope.theActivity.id }, $scope.theActivity, function (res) {
      Activity.get({ activityId: res.id })
        .$promise.then(function (activity) {
          $scope.successMsg   = activity.name + '修改完成!';
          $scope.activitys    = Activity.query();
        });
    });
  };

  /**
   * 移除活動，db + view 直接同步
   * 
   * @param  {integer} id [活動索引]
   * @return {void}    
   */
  $scope.remove = function (id) {
    if ( !window.confirm('確定要刪除嘛?' ) ) {
      return;
    }

    Activity.delete({ activityId: id })
      .$promise.then(function (res) {
        $scope.successMsg   = res.name + '刪除完成！';
        $scope.activitys    = Activity.query();
      }, function (error) {
        $scope.orphanMsg  = '此活動尚有關連商品，請轉出後再刪除!';
        $scope.activitys  = Activity.query();
      });
  };

  /**
   * 清空編輯之活動資料
   * 
   * @return {void}
   */
  $scope.clean = function () {
    $scope.theActivity = {
      id: '',
      name: '',
      description: '',
      startAt: '',
      endAt: ''
    };
  };

  /**
   * 檢查有無成功訊息
   * 
   * @return {Boolean} 
   */
  $scope.hasSuccessMsg = function () {
    return ($scope.successMsg.length > 0);
  };

  /**
   * 清空成功訊息
   * 
   * @return {Boolean} [shold be false]
   */
  $scope.emptySuccessMsg = function () {
    return $scope.successMsg = false;
  };

  /**
   * 檢查有無因存在關連無法刪除之訊息
   * 
   * @return {Boolean}
   */
  $scope.hasOrphanMsg = function () {
    return ($scope.orphanMsg.length > 0);
  };

  /**
   * 清空存在關連無法刪除之訊息
   * 
   * @return {Boolean} [shold be false]
   */
  $scope.emptyOrphanMsg = function () {
    return $scope.orphanMsg = false;
  };

  /**
   * 檢查 modal 表單是否合法
   * 
   * @return {void}
   */
  $scope.validateForm = function () {
    $scope.errorMsgs = [];
    $scope.hasError = false;
    if ( !$scope.theActivity.name || $scope.theActivity.name.length === 0 ) {
      $scope.errorMsgs.push( '請輸入活動名稱' );
      $scope.hasError = true;
    }

    if ($scope.theActivity.startAt.length === 0) {
      $scope.errorMsgs.push( '請指定起始時間' );
      $scope.hasError = true;
    }

    if ($scope.theActivity.endAt.length === 0) {
      $scope.errorMsgs.push( '請指定結束時間' );
      $scope.hasError = true;
    }
  };

  /**
   * 檢查是否存在表單驗證錯誤訊息
   * 
   * @return {Boolean}
   */
  $scope.hasErrorMsg = function () {
    return ($scope.errorMsgs.length > 0);
  };

  /**
   * 清空表單驗證錯誤訊息
   * 
   * @param  {integer} index [活動索引]
   * @return {json}          [表單驗證錯誤訊息]
   */
  $scope.emptyErrorMsg = function (index) {
    return (typeof index !== 'number') ? $scope.errorMsgs = [] : $scope.errorMsgs.splice(index, 1);
  };

  /**
   * 監聽 activitys，此值一有變化則延遲 0.5 秒關閉 modal
   * 
   * @return {void}
   */
  $scope.$watch('activitys', function () {
    setTimeout(function () {
      $('button.j-modal-close').click();
      $scope.clean();
    }, 500) // avoid $digest problem
  });
}]);