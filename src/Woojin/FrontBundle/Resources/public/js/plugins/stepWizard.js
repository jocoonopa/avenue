var StepWizard = function () {

    return {

        initStepWizard: function () {
            var $form = $(".shopping-cart");

                $form.validate({
                    errorPlacement: function errorPlacement(error, element) {  
                        error.insertBefore(element);
                    },
                    rules: {
                        name: {
                            required: true,
                        },
                        email: {
                            required: true,
                            email: true
                        },
                        phone: {
                            rangelength:[8, 10]
                        },
                        address: {
                            required: true,
                            maxlength: 100,
                            minlength: 4
                        }
                    },
                    messages: {
                        name: {
                            required: '請輸入您的稱呼'
                        },
                        email: {
                            required: '電子信箱為必填欄位',
                            email: '請輸入有效的電子信箱'
                        },
                        phone: {
                            rangelength: '電話長度必須在 8 ~ 10 個字元長度範圍'
                        },
                        address: {
                            required: '地址不可空白',
                            maxlength: '地址長度不可超過100字元',
                            minlength: '地址長度必須超過4個字元'
                        }
                    }
                });
                
                $form.children("div").steps({
                    labels: {
                        /**
                         * Label for the cancel button.
                         *
                         * @property cancel
                         * @type String
                         * @default "Cancel"
                         * @for defaults
                         **/
                        cancel: "取消",

                        /**
                         * This label is important for accessability reasons.
                         * Indicates which step is activated.
                         *
                         * @property current
                         * @type String
                         * @default "current step:"
                         * @for defaults
                         **/
                        current: "目前步驟:",

                        /**
                         * This label is important for accessability reasons and describes the kind of navigation.
                         *
                         * @property pagination
                         * @type String
                         * @default "Pagination"
                         * @for defaults
                         * @since 0.9.7
                         **/
                        pagination: "Pagination",

                        /**
                         * Label for the finish button.
                         *
                         * @property finish
                         * @type String
                         * @default "Finish"
                         * @for defaults
                         **/
                        finish: "完成",

                        /**
                         * Label for the next button.
                         *
                         * @property next
                         * @type String
                         * @default "Next"
                         * @for defaults
                         **/
                        next: "下一步",

                        /**
                         * Label for the previous button.
                         *
                         * @property previous
                         * @type String
                         * @default "Previous"
                         * @for defaults
                         **/
                        previous: "上一步",

                        /**
                         * Label for the loading animation.
                         *
                         * @property loading
                         * @type String
                         * @default "Loading ..."
                         * @for defaults
                         **/
                        loading: "讀取中..."
                    },
                    headerTag: ".header-tags",
                    bodyTag: "section",
                    transitionEffect: "fade",
                    onStepChanging: function (event, currentIndex, newIndex) {
                        // Allways allow previous action even if the current form is not valid!
                        if (currentIndex > newIndex) {
                            return true;
                        }

                        // 第一步 -> 第二步，
                        // 購物車數量 > 0
                        if (currentIndex == 0 && newIndex == 1 ) {
                            return (ac.getItemsCount() > 0);
                        }

                        $form.validate().settings.ignore = ":disabled,:hidden";

                        //var isValid = ($('input[name="isDiffAddress"]:checked').length === 0 || $('input[name="invoice_address"]').val().length > 5);
                        
                        // if (isValid) {
                        //     $('.invoice-info-error').addClass('hidden');

                        //     return true;
                        // } else {
                        //     $('input[name="invoice_address"]').focus();
                        //     $('.invoice-info-error').removeClass('hidden');

                        //     return false;
                        // }

                        return $form.valid();
                    },
                    onFinishing: function (event, currentIndex) {
                        $form.validate().settings.ignore = ":disabled";
                        
                        return $form.valid();
                    },
                    onFinished: function (event, currentIndex) {
                        //alert("Submitted!");
                        $form.submit();
                    }
                });
        }, 

    };
}();        