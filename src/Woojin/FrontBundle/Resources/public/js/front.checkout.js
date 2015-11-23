Login.initLogin();
StepWizard.initStepWizard();  

$('#phone').numeric();

$('a.trigger-submit').click(function () {
    $('a[href="#finish"]').click();

    return false;
});