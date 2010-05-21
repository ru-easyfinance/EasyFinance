function initTKSDialogs() {
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
            patronymic: "Введите отчество!",
            phone: "Введите телефон!"
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

                //$("#dlgTKSForm #divShortSuccess").show();
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

/* необходимо для конвертации из UTF-8 в Win-1251
 * для отправки GET-запроса на сайт Тинькова */

// Инициализируем таблицу перевода
var trans = [];
for (var i = 0x410; i <= 0x44F; i++)
  trans[i] = i - 0x350; // А-Яа-я
trans[0x401] = 0xA8;    // Ё
trans[0x451] = 0xB8;    // ё

// Сохраняем стандартную функцию escape()
var escapeOrig = window.escape;

// Переопределяем функцию escape()
window.escape = function(str)
{
  var ret = [];
  // Составляем массив кодов символов, попутно переводим кириллицу
  for (var i = 0; i < str.length; i++)
  {
    var n = str.charCodeAt(i);
    if (typeof trans[n] != 'undefined')
      n = trans[n];
    if (n <= 0xFF)
      ret.push(n);
  }
  return escapeOrig(String.fromCharCode.apply(null, ret));
}


function submitTKSFull() {
    if ($("#frmTKSShort").valid()) {
        // отслеживаем событие в Google Analytics
        if (_gaq) {
            _gaq.push(['_trackEvent', 'Анкета', 'Переход', 'ТКС - полная анкета']);
        }

        submitTKSinternal();

        // направляем на сайт Тинькова
        var prefix = "https://www.tcsbank.ru/deposit/form/?easyfinance_formdeposit&";
        var params = 'surname=' + escape($("#txtSurname").val()) + '&firstname=' + escape($("#txtName").val()) + '&lastname=' + escape($("#txtPatronymic").val()) + '&phone=' + $("#txtPhone").val();
        var postfix = '&step=2';
        var url = prefix + params + postfix;
        window.open(url);

        $("#dlgTKSForm #imgWaiting1").show();
    }
}

$(document).ready(function() {
    /*
    $("#promoTKS").accordion({
        autoHeight: false
    });
    */

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

