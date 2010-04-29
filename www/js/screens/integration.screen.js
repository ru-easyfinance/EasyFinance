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
    });

    if (!res.profile.integration.email || res.profile.integration.email=='') {
        // email не создан, открываем этап генерации e-mail
        easyFinance.widgets.userIntegrations.load({});
        $("#integrationSteps").accordion("activate" , 1);
    } else {
        // email создан, запоминаем
        easyFinance.widgets.userIntegrations.load({service_mail : res.profile.integration.email});
        // следующий шаг - привязка счёта
        $("#btnIntegrationMailNext").show();
        $("#integrationSteps").accordion("activate" , 2);
    }

    // ======================================================

    // #1232. создание счёта
    easyFinance.widgets.accountEdit.init('#widgetAccountEdit', easyFinance.models.accounts, easyFinance.models.currency);
    
    // переход на предыдущий этап создания email'a
    $('#btnBackToEmail').click(function() {
        $("#integrationSteps").accordion("activate" , 1);
    });

    // переход на следующий этап
    $('#btnCreateAccount').click(function() {
        // отображает форму создания счёта
        easyFinance.widgets.accountEdit.addAccount();
    });

    // переход на следующий этап
    $('#btnLinkAccount').click(function() {
        if (!$("#optionAccount").val() ) {
            $.jGrowl("Создайте и выберите счёт!", {theme: 'red'});
        } else {
            $("#integrationSteps").accordion("activate" , 2);
        }
    });
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
