$('.shopping-cart .product-it-in').css('margin-top', 0);

// Open Modal
// Show modal
$('.show-modal-trigger').click(function () {
    // Filled Content
    // (You need build an API for this)
    var $this = $(this);
    var $modal = $('#behalfShow');

    $modal.find('h4').html('<b>' + $this.data('name') + '</b> 代購詳細資訊');
    var template = $('#behalfShow-modal-body-' + $this.data('id')).html();

    $modal.find('.modal-body').html(template);
    $modal.modal('show');
});

// bank modal
$('.bank-modal-trigger').click(function () {
    var $this = $(this);
    var $modal = $('#behalfBank');

    $modal.find('#behalfBankLabel').html('<h4>' + $this.data('name') + '</h4>');
    $modal.modal('show');
    $modal.find('form').attr('action', Routing.generate('front_behalf_bankinfo', {id: $this.data('id')}));
});

$('form#behalf-bank-info').validate({
    errorPlacement: function errorPlacement(error, element) {  
        error.insertAfter(element);
    },
    rules: {
        bankAccount: {
            required: true,
            rangelength:[5, 5],
            number: true
        },

        bankCode: {
            required: true,
            rangelength:[3, 3],
            number: true
        }
    },
    messages: {
        bankAccount: {
            required: '此欄位不可空白',
            rangelength: '長度不合法',
            number: '請輸入數字'
        },
        bankCode: {
            required: '此欄位不可空白',
            rangelength: '長度不合法',
            number: '請輸入數字'
        }
    }
});

$('form#behalf-bank-info').find('input').numeric({ negative: false });


// Form validate 


// Submit and redirect
// (You need a controller to handle this task)
