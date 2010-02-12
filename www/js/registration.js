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
                    },
                    mail_confirm: {
                        required: true,
                        email: true,
                        equalTo: $("#mail")
                    }
                },
                messages: {
                    name: "Вы не ввели имя",
                    login: "Вы не ввели логин",
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
                    $.post(
                        "/registration/new_user/",
                        {
                            password: $('#passw').val(),
                            confirm_password: $('#confirm_password').val(),
                            login: $('#log').val(),
                            name: $('#name').val(),
                            mail: $('#mail').val()
                        },
                        function (data){
                            if (!data || (!data.errors)||(data.errors == 'succes')){
                                window.location = data.result.redirect;
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
