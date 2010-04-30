function initGuest() {
    var _$dlgRegistration = $("#registration").dialog({
        title: "Регистрация",
        bgiframe: true,
        autoOpen: false,
        modal:true
    });

    $("#btnShowRegister").click(function() {
        _$dlgRegistration.dialog('open');
    });

    var _$dlgAuthentication = $("#authentication").dialog({
        title: "Вход в систему",
        bgiframe: true,
        autoOpen: false,
        modal:true
    });

    $("#btnShowLogin").click(function() {
        _$dlgAuthentication.dialog('open');
    });
}

function initLogged() {
    // #1242 для создания email'a
    easyFinance.widgets.userIntegrations.init();

    // переход на следующий этап после создания email'a
    $('#btnIntegrationMailNext').click(function() {
        $("#integrationSteps").accordion("activate" , 2);
        $("#wz_card_account_mail").val($("#lblIntegrationEmail").text());
    });

    if (!res.profile.integration.email || res.profile.integration.email=='') {
        // email не создан, открываем этап генерации e-mail
        easyFinance.widgets.userIntegrations.load({});
        $("#integrationSteps").accordion("activate" , 1);
    } else {
        // email создан, подставляем в виджет и анкету
        $("#wz_card_account_mail").val(res.profile.integration.email);
        easyFinance.widgets.userIntegrations.load({service_mail : res.profile.integration.email});
        // следующий шаг - привязка счёта
        $("#btnIntegrationMailNext").show();
        $("#integrationSteps").accordion("activate" , 2);
    }

    // ======================================================

    // #1232. создание и привязка счёта
    // выводим список счетов
    refreshAccounts();

    // виджет создания счёта
    easyFinance.widgets.accountEdit.init('#widgetAccountEdit', easyFinance.models.accounts, easyFinance.models.currency);

    // переход на предыдущий этап создания email'a
    $('#btnBackToEmail').click(function() {
        $("#integrationSteps").accordion("activate" , 1);
    });

    // открываем диалог создания счёта
    $('#btnCreateAccount').click(function() {
        // отображает форму создания счёта
        easyFinance.widgets.accountEdit.addAccount();
        // тип счёта жестко задан - дебетовая карта
        $("#acc_type").find("option:nth-child(2)").attr("selected", "selected").parent().attr("disabled", "disabled");
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
        $("#acc_name").val("EasyFinance AMT").focus();
    });

    // после добавления счёта обновляем список счетов
    $(document).bind('accountAdded', refreshAccounts);

    // переход на следующий этап
    $('#btnLinkAccount').click(linkAccount);

    // #1230. заполнение анкеты
    $('#btnBackToAccount').click(function() {
        $("#integrationSteps").accordion("activate" , 2);
    });
}

function refreshAccounts(event) {
    var $select = $("#optionAccount").empty();

    var account_list_ordered = easyFinance.models.accounts.getAccountsOrdered();
    if (!account_list_ordered || account_list_ordered.length == 0){
        return;
    }

    for (var row in account_list_ordered) {
        // выводим только дебетовые счета
        if (account_list_ordered[row]["type"] == "2") {
            $select.
                append($("<option></option>").
                attr("value", account_list_ordered[row]['id']).
                text(account_list_ordered[row]["name"]));
        }
    }

    // выбираем счёт после добавления
    if (event) {
        $("#optionAccount").find("option[value='" + event.id + "']").attr("selected", "selected");
    }
}

function linkAccount() {
    if (!$("#optionAccount").val() ) {
        $.jGrowl("Создайте и выберите счёт!", {theme: 'red'});
    } else {
        alert('@TODO: запрос на сервер');

        // открываем анкету
        $("#integrationSteps").accordion("activate" , 3);
       
        // подставляем в анкету нужную валюту
        var cur = easyFinance.models.accounts.getAccountCurrencyId($("#optionAccount").val());
        $("#wz_card_currency").find("option[value='" + (parseInt(cur) - 1) + "']").attr("selected", "selected");
    }
}

$(document).ready(function(){
    $("#integrationSteps").accordion({
        autoHeight: false,
        event: ""
    });

    if (!res || !res.profile) {
        // если пользователь не залогинен,
        // инициализируем диалоги регистрации
        // и аутентификации

        initGuest();
    } else {
        // инициализируем блоки шагов
        // и определяем по res,
        // какой шаг показать

        initLogged();
    }

    /* // @TEST
    if (document.location.pathname.indexOf("integration") != -1) {
        $.jGrowl("Регистрация успешно завершена!", {theme: 'green'});
        $("#integrationSteps").accordion("activate" , 1);
    }
    */
});
