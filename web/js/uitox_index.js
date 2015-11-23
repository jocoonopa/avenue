$(document).on('click', 'button.delete', function () {
    $(this).closest('div.form-group').remove();
});

$('button.add-div').click(function () {
    var $div = $('div.data-div').last().clone();

    $div.append('&nbsp;&nbsp;<button type="button" class="delete btn btn-danger btn-sm pull-left">刪除</a>');

    $div.insertBefore($('div.data-div').eq(0));
});