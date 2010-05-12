function initTKSDialogs() {
    var _$dlgTKSForm = $("#dlgTKSForm").dialog({
        title: "Анкета",
        bgiframe: true,
        width: "340px",
        autoOpen: false,
        modal:true
    });

    $("#btnFillForm").click(function() {
        _$dlgTKSForm.dialog('open');
        _$dlgTKSForm.find("#imgWaiting").hide();
        _$dlgTKSForm.find("#divShortSuccess").hide();
    });

    $("#btnCloseDialog").click(function() {
        $(this).hide();
        _$dlgTKSForm.dialog('close');
    });

    $.validator.addMethod(
        "phone",
        function(value,element){
            return (!isNaN(value) && value.toString().substr(0,1) == "8");
        },
        "Введите телефон начиная с 8-ки, в формате <b>8905</b>1234567 (без дефисов и скобок)."
    );

    $("#frmTKSShort").validate({
        rules: {
            name: "required",
            surname: "required",
            patronymic: "required",
            phone: "required phone"
        },
        messages: {
            name: "Введите имя!",
            surname: "Введите фамилию!",
            patronymic: "Введите отчество!"
        }
    });

    $("#frmTKSFull").validate({
        rules: {
            serial: "required",
            number: "required",
            where: "required",
            code: "required",
            date: "required"
        },
        messages: {
            serial: "Введите серию!",
            number: "Введите номер!",
            where: "Введите место выдачи!",
            code: "Введите код!",
            date: "Введите дату!"
        }
    });
}

function submitTKSinternal() {
    $("#dlgTKSForm #imgWaiting1").show();
    $.post('/promo/tks/anketa', {
        surname: $("#txtSurname").val(),
        name: $("#txtName").val(),
        patronymic: $("#txtPatronymic").val(),
        email: $("#txtEmail").val(),
        phone: $("#txtPhone").val()
    }, function(data) {
        $("#dlgTKSForm #imgWaiting1").hide();

        if (data) {
            if (data.error) {
                if (data.error.text)
                    $.jGrowl(data.error.text, {theme: 'red', life: 2500});
            } else if (data.result) {
                //if (data.result.text)
                //    $.jGrowl(data.result.text, {theme: 'green', life: 2500});

                $("#dlgTKSForm #btnSubmitShort").val("Отправить обновлённую");
                $("#dlgTKSForm #divShortSuccess").show();
            }
        } else {
            $.jGrowl("Ошибка на сервере!", {theme: 'red', life: 2500});
        }
    }, "json");
}

function submitTKSShort() {
    if ($("#frmTKSShort").valid()) {
        // отслеживаем событие в Google Analytics
        if (_gaq) {
            _gaq.push(['_trackEvent', 'Анкета', 'Заполнена', 'ТКС - короткая анкета']);
        }

        submitTKSinternal();
    }
}

function submitTKSFull() {
    if ($("#frmTKSShort").valid()) {
        // отслеживаем событие в Google Analytics
        if (_gaq) {
            _gaq.push(['_trackEvent', 'Анкета', 'Переход', 'ТКС - полная анкета']);
        }

        submitTKSinternal();

        // @TODO: window.open - направляем на сайт Тинькова
        window.open("https://www.tcsbank.ru/deposit/form/");

        $("#dlgTKSForm #imgWaiting1").show();
    }
}

$(document).ready(function() {
    $("#promoTKS").accordion({
        autoHeight: false
    });

    initTKSDialogs();

    $("#promoTKS #btnNext").click(function() {
        $("#promoTKS").accordion("activate", 1);
        return false;
    });

    $("#promoTKS #btnPrev").click(function() {
        $("#promoTKS").accordion("activate", 0);
        return false;
    });

    // виджет создания счёта
    easyFinance.widgets.accountEdit.init('#widgetAccountEdit', easyFinance.models.accounts, easyFinance.models.currency);

    // открываем диалог создания счёта
    $('#promoTKS #btnCreateAccount').click(function() {
        // отображает форму создания счёта
        easyFinance.widgets.accountEdit.addAccount();
        // тип счёта жестко задан - дебетовая карта
        $("#acc_type").find("option:nth-child(4)").attr("selected", "selected").parent().attr("disabled", "disabled");
        // удаляем все валюты кроме РУБ, USD, EUR
        $("#acc_currency").find("option").each(function () {
            var val = $(this).val();
            if (val != "1" && val != "2" && val != "3") {
                $(this).remove();
            }
        });

        // выбираем первую валюту
        $("#acc_currency").find("option:first").attr("selected", "selected");
        // имя счёта по умолчанию
        $("#acc_name").val("TKS").focus();
    });

    $('#dlgTKSForm #btnSubmitShort').click(submitTKSShort);
    $('#dlgTKSForm #btnSubmitFull').click(submitTKSFull);

    // после добавления счёта обновляем список счетов
    $(document).bind('accountAdded', function(){/*alert('Счёт успешно создан!')*/});
});

