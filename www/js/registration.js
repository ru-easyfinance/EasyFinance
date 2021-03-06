$(document).ready(function() {
    function registrationCallback(data) {
        registrationCanClick = true;

        if (data) {
            if (data.error) {
                if (data.error.text) {
                    $.jGrowl(data.error.text, {theme: 'red', life: 2500});
                } else if (data.error.redirect) {
                    setTimeout(function(){window.location = data.error.redirect;},3000);
                }
            } else if (data.result) {
                if (document.location.pathname.indexOf("integration") != -1) {
                    // #1215 регистрация на странице интеграции
                    // перезагружаем страницу.
                    // поскольку пользователь будет уже залогинен,
                    // после обновления страницы нужные данные будут в res
                    window.location.reload();
                } else {
                    $("#lblRegistrationStatus").append("<br>Регистрация успешно завершена! Теперь Вы можете <a href=\"/my/review/\">войти в систему</a>.<br>(Вы будете автоматически направлены на страницу &laquo;Обзор&raquo; через несколько секунд)");

                    if (data.result.redirect)
                        setTimeout(function(){window.location.href = data.result.redirect;},3000);
                }
            }
        } else {
            $.jGrowl('Ошибка на сервере!', {theme: 'red'});
        }
    }

    var registrationCanClick = true;

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
                        minlength: 5,
                        latinAndDigits: true
                    },
                    confirm_password: {
                        required: true,
                        minlength: 5,
                        latinAndDigits: true,
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

            // Svel: t1629 - сабмит формы с Enter
            $("#formRegister").keypress(function(e){
                //if generated character code is equal to ascii 13 (if enter key)
                if(e.keyCode == 13){
                    //submit the form
                    $('#butt').click();
                    return false;
                } else {
                    return true;
                }
            });

            /**
             * пересылка данных из формы на сервер
             */
            $('#butt').click(function(){
                if (registrationCanClick == false) {
                    // запрос уже в процессе выполнения
                    return false;
                }

                if ($("#mail").val() != $("#mail_confirm").val()) {
                    $.jGrowl('Введённые Вами E-Mail адреса должны совпадать!',{theme:'red', stick: true});
                    return false;
                }

                if ($("#formRegister").valid()) {
                    // изменил вывод сообщений, см. тикет #1128
                    $("#lblRegistrationStatus").removeClass("hidden");
                    registrationCanClick = false;

                    $.post(
                        "/registration/new_user/?responseMode=json",
                        {
                            password: trim($('#passw').val()),
                            confirm_password: trim($('#confirm_password').val()),
                            login: trim($('#log').val()),
                            name: trim($('#name').val()),
                            mail: trim($('#mail').val())
                        },
                        registrationCallback,
                        'json');
                }
            });

            break;
    }
});

