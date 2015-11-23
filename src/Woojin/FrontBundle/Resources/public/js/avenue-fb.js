/**
 * Avenue FB 登入相關方法集成
 *
 * @param {string} appId [開發者的 appId]
 * @param {string} appVerifyUrl 
 * [
 *     fb 個人資料完成後導向開發者 app 的 url，
 *     這邊會透過自動產生 form 的方式 已POST 傳送兩個參數 jAuthResponse 以及 jInformation，
 *     即驗證回傳物件以及個人資訊回傳物件的 JSON 字串
 * ]
 * 
 * @depend {jQuery, fos.routing.js}
 */
var AvenueFacebookHandler = function (appId, appVerifyUrl) {
    this.appId = appId;
    this.url = appVerifyUrl;
    this.init();
};

/**
 * 初始化 FB 物件
 */
AvenueFacebookHandler.prototype.init = function () {
    var self = this;

    /**
     * 宣告fb 非同步初始化事件
     */
    // window.fbAsyncInit = function() {
    //     FB.init({
    //         appId: self.appId,
    //         cookie: true,  // enable cookies to allow the server to access 
    //                     // the session
    //         xfbml: true,  // parse social plugins on this page
    //         version: 'v2.2' // use version 2.1
    //     });
    // };

    // Load the SDK asynchronously
    // (function(d, s, id) {
    //     var js, fjs = d.getElementsByTagName(s)[0];
    //     if (d.getElementById(id)) return;
    //     js = d.createElement(s); js.id = id;
    //     js.src = "//connect.facebook.net/zh_TW/sdk.js";
    //     fjs.parentNode.insertBefore(js, fjs);
    // }(document, 'script', 'facebook-jssdk'));

    return this;
};

/**
 * 透過FB物件登入avenue
 *
 * 1. FB.login 會先和 FB 進行第一步驗證
 * 2. 通過後，執行驗證成功的CallBack, 該Callback 會取得使用者的個人資訊
 * 3. 取得個人資訊成功後，將資料放入隱藏的表單裡提交
 */
AvenueFacebookHandler.prototype.login = function () {
    var self = this;

    return FB.login(function(authResponse) {
        if ('connected' === authResponse.status) {
            self.loginSuccessCallback(authResponse);
        } else if ('not_authorized' === authResponse.status) {
            alert('開發者請登入應用程式喔!');
        } else {
            alert('請登入臉書喔!');
        }
    }, {scope: 'public_profile,email'});
};

/**
 * 驗證確定成功登入的 callBack
 *
 * @param {object} authResponse
 */
AvenueFacebookHandler.prototype.loginSuccessCallback = function (authResponse) {  
    var self = this;

    return FB.api('/me', function(information) {
        self.redirecToAvenueViaAutosubmitForm(authResponse, information);
    });
};

/**
 * 透過自動提交一個隱藏表單導向 avenue後台完成登入 | 註冊
 * 
 * @param  {object} authResponse
 * @param  {object} information 
 */
AvenueFacebookHandler.prototype.redirecToAvenueViaAutosubmitForm = function (authResponse, information) {
    var $form = $('<form action="' + this.url + '" name="avenueFbLogin" method="POST"></form>');
    var jAuthResponse = JSON.stringify(authResponse);
    var jInformation = JSON.stringify(information);

    $form
        .append('<input type="hidden" name="jAuthResponse" />')
        .append('<input type="hidden" name="jInformation" />')
    ;

    $form.find('input[name="jAuthResponse"]').val(jAuthResponse);
    $form.find('input[name="jInformation"]').val(jInformation);

    return $form.submit();
};

/**
 * FB 的登出方法(應該是用不太到)
 */
AvenueFacebookHandler.prototype.facebookLogout = function () {
    FB.logout(function () {
        window.location.reload();
    });

    return this;
};
