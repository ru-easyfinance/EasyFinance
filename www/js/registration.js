$().ready(function() {
	$("#formRegister").validate({
		rules: {
			name: "required",
			login: "required",
			password: {
				required: true,
				minlength: 5
			},
			confirm_password: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			},
			mail: {
				required: true,
				email: true
			}
		},
		messages: {
			name: "Вы не ввели имя!",
			login: "Вы не ввели логин!",
			password: {
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

	// check if confirm password is still valid after password changed
	$("#password").blur(function() {
		$("#confirm_password").valid();
	});

});