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
			name: "�� �� ����� ���!",
			login: "�� �� ����� �����!",
			password: {
				required: "�� �� ����� ������!",
				minlength: "��� ������ ������ �������� ��� ������� �� 5 ��������"
			},
			confirm_password: {
				required: "�� �� ����� ������!",
				minlength: "��� ������ ������ �������� ��� ������� �� 5 ��������",
				equalTo: "����������, ������� ��� �� ������, ��� � ����"
			},
			mail: "����������, ������� ���������� ����� ����������� �����!"
		}
	});

	// check if confirm password is still valid after password changed
	$("#password").blur(function() {
		$("#confirm_password").valid();
	});

});