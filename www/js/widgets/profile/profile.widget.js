easyFinance.widgets.profile = function(){
    function init(){
        easyFinance.models.user.reload(setupProfile);
//        $('#back_info').click(function(){
//            setupProfile();
//        });
        $('#save_info').click(function(){
            sendProfile();
        });
    }
    
    function setupProfile(){
        var data = easyFinance.models.user.getUserInfo();
        $('input#login').val(data.login);
        $('input#mail').val(data.mail);
        if (data.tooltip == '0'){
            $('#help').removeAttr('checked');
        }
        if (!data.guide){
            $('#guide').removeAttr('checked');
        }
        if (data.getNotify == "0")
            $('#getNotify').removeAttr('checked');
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
            mail : $('#mail').val()
        }
        easyFinance.models.user.setUserInfo(data,function(data){
            $.jGrowl("Личные данные сохранены", {theme: 'green'});
            //setupProfile();
        });
    }
    return {
        init : init,
        setupProfile : setupProfile,
        sendProfile : sendProfile
    }
}();

//to screen
$(document).ready(function() {
    $('.menu5 #i3').addClass('act');
    $('.menu5 #i3').live('click',function(){
        $('.menu5 #i4').removeClass('act');
        $('.menu5 #i3').addClass('act');
        $('.block2 .ramka3#money').hide();
        $('.block2 .ramka3#profile').show();
    });
    $('.menu5 #i4').live('click',function(){
        $('.menu5 #i3').removeClass('act');
        $('.menu5 #i4').addClass('act');
        $('.block2 .ramka3#profile').hide();
        $('.block2 .ramka3#money').show();
    });
    easyFinance.widgets.profile.init();
});