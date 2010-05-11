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
    });
}

function submitTKSShort() {
    $("#dlgTKSForm #imgWaiting").show();

    setTimeout( function() { $("#dlgTKSForm #imgWaiting").hide() }, 2000);
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

    // после добавления счёта обновляем список счетов
    $(document).bind('accountAdded', function(){/*alert('Счёт успешно создан!')*/});
});

