$(document).ready(function() {
	$("#formRegister").validate({
		rules: {
			name: "required",
			login: "required",
			passw: {
				required: true,
				minlength: 5
			},
			confirm_password: {
				required: true,
				minlength: 5,
				equalTo: $("#passw")
			},
			mail: {
				required: true,
				email: true
			}
		},
		messages: {
			name: "Вы не ввели имя!",
			login: "Вы не ввели логин!",
			passw: {
				required: "Вы не ввели пароль!",
				minlength: "Ваш пароль должен состоять как минимум из 5 символов"
			},
			confirm_password: {
				required: "Вы не ввели пароль!",
				minlength: "Ваш пароль должен состоять как минимум из 5 символов",
				equalTo: "Пожалуйста, введите тот же пароль, что и выше"
			},
			mail: "Пожалуйста, введите правильный адрес электронной почты!"
		}
	});
        $('#butt').click($("#formRegister").validate);
	// check if confirm password is still valid after password changed
	$("#password").blur(function() {
		$("#confirm_password").valid();
	});
        //if (window.location.hash=='#send')
        $('#butt').click(function()
        {
            
            /*$.post("/registration/new_user/",{

            },function(data) {

            },'json');*/
            //$.jGrowl('На указанный при регистрации e-mail отправлено письмо со ссылкой, \n\
            //            перейдя по которой, вы активируете вашу учетную запись', {theme: 'green', stick: true});
            if ($("#formRegister").valid()) {
                $.post("/registration/new_user/",{
                    password: $('#passw').val(),
                    confirm_password: $('#confirm_password').val(),
                    login: $('#log').val(),
                    name: $('#name').val(),
                    mail: $('#mail').val()
                })
                $('.formRegister').hide();
                $('#formConfirm').show();
                //$('#formConfirm').dialog('option', 'stack', true);
                /*$('#formConfirm').dialog({
                })*/

                //$('.formConfirm').show();
            }
            
        })
        if (location.hash == '#activate'){
            $('.formRegister').hide();
            $('#formConfirm').show().css('height','200px');
            $('#formConfirm h2').text('Регистрация завершена')
            $('#formConfirm .inside').html('<p>Поздравляем, Ваша учётная запись успешно активирована.</p>\n\
                <p>Теперь Вы можете авторизироваться в системе. </p>\n\
                <p>Для входа, нужно щёлкнуть по иконке в правом верхнем углу экрана, или перейти по ссылке \n\
                    <a href="https://easyfinance.ru/login/">https://easyfinance.ru/login/</a>\n\
                 и указать свой логин и пароль.</p>')
        }
});