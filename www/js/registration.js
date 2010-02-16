$(document).ready(function() {
    var hash = window.location.hash;
    if (!hash) {hash = '';}
    //////////////////////////////////////////////////////////////////////
    switch (hash){
        case '#activate':
            $('#registration').hide();
            $('#activation').show();
            break;
        default :
            /**
             * Условия валидации формы
             */
			 
			jQuery.validator.addMethod("latinAndDigits", function(value, element) {
				return this.optional(element) || !/[^a-zA-Z0-9]/.test(value); 
			}, "Данное поле должно содержать только латинские буквы и символы!");

			 
            $("#formRegister").validate({
                rules: {
                    name: "required",
                    login: {
						required: true,
						latinAndDigits: true
					},
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
                    },
                    mail_confirm: {
                        required: true,
                        email: true,
                        equalTo: $("#mail")
                    }
                },
                messages: {
                    name: "Вы не ввели имя",
                    login: {
						require: "Вы не ввели логин",
						latin: "Логин должен состоять из латинских букв и цифр"
					},
                    passw: {
                        required: "Вы не ввели пароль",
                        minlength: "Ваш пароль должен состоять как минимум из 5 символов"
                    },
                    confirm_password: {
                        required: "Вы не ввели пароль",
                        minlength: "Ваш пароль должен состоять как минимум из 5 символов",
                        equalTo: "Пожалуйста, введите тот же пароль, что и выше"
                    },
                    mail: "Пожалуйста, введите правильный адрес электронной почты",
                    mail_confirm: {
                        required: "Вы не ввели e-mail",
                        equalTo: "Пожалуйста, введите тот же e-mail, что и выше"
                    }

                }
            });
            /*
             * Евенты, вызывающие валидацию
             */
            $('#butt').click($("#formRegister").validate);
            $("#password").blur(function() {
                $("#confirm_password").valid();
            });
            $("#mail_confirm").blur(function() {
                $("#mail_confirm").valid();
            });
            /**
             * пересылка данных из формы на сервер
             */
            $('#butt').click(function(){
                if ($("#formRegister").valid()) {
                    $('.formRegister').hide();
					$.jGrowl('Спасибо, Ваш запрос о регистрации отправлен',{theme:'green'});
                    $.post(
                        "/registration/new_user/?responseMode=json",
                        {
                            password: $('#passw').val(),
                            confirm_password: $('#confirm_password').val(),
                            login: $('#log').val(),
                            name: $('#name').val(),
                            mail: $('#mail').val()
                        },
                        function (data){
                            if (!data || (!data.errors)||(data.errors == 'succes')){
				var redir = data.result.redirect
				$.jGrowl(data.result.text,{theme:'green'})
                                setTimeout(function(){window.location = redir;},3000);
                            }else{
                                $('.formRegister').show();
                                var str = '<ul>'
                                for (var key in data.errors){
                                    str += '<li>' + data.errors[key] + '</li>'
                                }
                                $.jGrowl('<div><b>При регистации возникли ошибки :</b></div>' + str + '</ul>' , {theme: 'red'})
                            }
                        },
                        'json')
                    }
                })
                break;
	}
});
