
easyFinance.widgets.profile = function(model){
    var _model = model || easyFinance.models.user;
    function init(){
        _model.reload(setupProfile);
//        $('#back_info').click(function(){
//            setupProfile();
//        });
        $('#save_info').click(function(){
            sendProfile();
        });
    }

    function setupProfile(){
        var data = _model.getUserInfo();
        $('input#login').val(data.login);
        $('input#mailIntegration').val( res.profile.integration && res.profile.integration.email || '');
        $('input#mail').val(data.mail);
        if (data.tooltip == '0'){
            $('#help').removeAttr('checked');
        }
        if (!data.guide){
            $('#guide').removeAttr('checked');
        }
        if (data.getNotify == "0")
            $('#getNotify').removeAttr('checked');
        easyFinance.widgets.operationReminders.init("#reminders", easyFinance.models.user, "profile");
    }

    function sendProfile(){
        if($('input#newpass').val() && ( !$('input#pass').val() || $('input#pass').val() == '' )){
            $.jGrowl("Пожалуйста введите пароль", {theme: 'red'});
            return;
        }
        if($('input#newpass').val() && $('input#newpass').val() != $('input#newpass2').val()){
            $.jGrowl("Неверно заполнено поле повтора пароля", {theme: 'red'});
            return;
        }
        var data = {
            tooltip : $('#help:checked').length,
            guide : ($('#guide:checked').length > 0 ? 'uyjsdhf' : ''),//@todo guid
            getNotify : $('#getNotify:checked').length.toString(),
            login : $('#login').val(),
            password : $('#pass').val(),
            newPassword : $('#newpass').val(),
            confirmpass : $('#newpass2').val(),
            mail : $('#mail').val(),
            mailIntegration: $('#mailIntegration').val()
        }
        _model.setUserInfo(data, function(data){
            if (data.result && data.result.text) {
                $.jGrowl(data.result.text, {theme: 'green'});
            } else if (data.error && data.error.text) {
                $.jGrowl(data.error.text, {theme: 'red'});
            }
        });
    }
    return {
        init : init,
        setupProfile : setupProfile,
        sendProfile : sendProfile
    }
}(easyFinance.models.user);
