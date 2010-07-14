// {* $Id: operation.js 137 2009-08-10 16:00:50Z ukko $ *}
    /*var catlast ;
    var datelast ;*/

/**
 * Функция очищает форму ввода операции
 */
function clearForm() {
    $('#op_type,#op_category,#op_target').val(0);
    $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags,#op_date').val('');

    $('span#op_amount_target').text();

    $('span#op_amount_done').text();
    $('span#op_forecast_done').text();
    $('span#op_percent_done').text();

    $('#op_close').removeAttr('checked');

    $('form').attr('action','/operation/add/');

    $('#op_type').change();
}


$(document).ready(function() {
    // загружаем журнал транзакций
    // по умолчанию показываются операции по всем счетам
    
    // обрабатываем хэш - по какому счёту выводить операции
    var account = window.location.hash;
    if (account.indexOf("#account") != -1) {
        account = account.replace("#account=", "");
    } else {
        account = '';
    }

    if (account != ''){
        // #870. //easyFinance.widgets.operationEdit.showForm();
        easyFinance.widgets.operationEdit.setAccount(account);
    }
    easyFinance.widgets.operationReminders.init("#operationEdit_reminders", easyFinance.models.user, "operation")

    easyFinance.widgets.operationsJournal.setAccount(account);
    easyFinance.widgets.operationsJournal.loadJournal();
});
