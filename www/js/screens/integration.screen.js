$(function() {
    $("#integrationSteps").accordion({
        autoHeight: false,
        event: ""
    });

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

	easyFinance.widgets.userIntegrations.init();
	easyFinance.widgets.userIntegrations.load(res.profile.integration.email && res.profile.integration.email != '' ? {service_mail : res.profile.integration.email} : {});

    /* // @TEST
    if (document.location.pathname.indexOf("integration") != -1) {
        $.jGrowl("Регистрация успешно завершена!", {theme: 'green'});
        $("#integrationSteps").accordion("activate" , 1);
    }
    */
});
