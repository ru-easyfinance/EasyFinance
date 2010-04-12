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
                        required: "Вы не ввели логин",
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
                    mail: {
                        required: " ",
                        email: "Пожалуйста, введите правильный адрес электронной почты"
                    },
                    mail_confirm: {
                        required: " ",
                        email: "Пожалуйста, введите правильный адрес электронной почты",
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
                    //$.jGrowl('Спасибо, Ваш запрос о регистрации отправлен',{theme:'green'});
                    // изменил вывод сообщений, см. тикет #1128
                    $("#lblRegisrationStatus").removeClass("hidden");
                    
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
                            if (data) {
                                if (data.error) {
                                    if (data.error.text)
                                        $.jGrowl(data.error.text, {theme: 'red', life: 2500});

                                    if (data.error.redirect)
                                        setTimeout(function(){window.location = data.error.redirect;},3000);
                                } else if (data.result) {
                                    $("#lblRegistrationStatus").append("<br>Регистрация успешно завершена! Теперь Вы можете <a href=\"/login\">войти в систему</a>.<br>(Вы будете автоматически направлены на страницу входа через несколько секунд)");

                                    //if (data.result.text)
                                    //    $.jGrowl(data.result.text, {theme: 'green'});

                                    if (data.result.redirect)
                                        setTimeout(function(){window.location = data.result.redirect;},3000);
                                }
                            } else {
                                $.jGrowl('Ошибка на сервере!', {theme: 'red'});
                            }
                        },
                        'json')
                    }
                })
                break;
	}
});
