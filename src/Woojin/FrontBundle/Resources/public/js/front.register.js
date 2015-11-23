// Use Symfony Validate is Enough
//Registration.initRegistration();   

$('input#woojin_orderbundle_custom_mobil').numeric();

$('input[name="isAgree"]').change(function () {
    var isChecked = $('input[name="isAgree"]:checked').length > 0;

    $('button[type="submit"]').prop('disabled', !isChecked);
});

$('a.captcha_reload').html('<i class="glyphicon glyphicon-refresh"></i>');