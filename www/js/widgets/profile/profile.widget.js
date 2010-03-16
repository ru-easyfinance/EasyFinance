;
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
$(document).ready(function(){
	$('.menuProfile li').click(function(){
        $('.menuProfile li').removeClass('act');
        $(this).addClass('act');
        $('.block2 .ramka3.profile').hide();
        $('.block2 .ramka3'+$(this).attr('block')).show();
    });
    easyFinance.widgets.profile.init();
	
	
    if (window.location.hash.indexOf("#currency") != -1)
        $('.menuProfile #i4').click();
});